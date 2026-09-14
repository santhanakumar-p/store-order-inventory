<?php

use App\Models\Order;
use App\Models\Product;
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

it('allows only one concurrent order when stock is limited to one', function () {
    $database = database_path('testing-race.sqlite');

    if (file_exists($database)) {
        unlink($database);
    }

    touch($database);

    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => $database,
        'database.connections.sqlite.foreign_key_constraints' => true,
        'database.connections.sqlite.busy_timeout' => 5000,
        'database.connections.sqlite.transaction_mode' => 'IMMEDIATE',
    ]);

    DB::purge('sqlite');
    DB::reconnect('sqlite');

    Artisan::call('migrate', ['--force' => true]);

    $product = Product::factory()->create([
        'qty' => 1,
        'selling_price' => 100.00,
        'tax_rate' => 18.00,
    ]);

    $productId = $product->id;

    $firstPayload = json_encode([
        'customer' => [
            'name' => 'Race Tester One',
            'email' => 'race-one@example.com',
        ],
        'items' => [
            [
                'product_id' => $productId,
                'qty' => 1,
            ],
        ],
    ], JSON_THROW_ON_ERROR);

    $secondPayload = json_encode([
        'customer' => [
            'name' => 'Race Tester Two',
            'email' => 'race-two@example.com',
        ],
        'items' => [
            [
                'product_id' => $productId,
                'qty' => 1,
            ],
        ],
    ], JSON_THROW_ON_ERROR);

    $script = base_path('tests/Support/race-create-order.php');

    $env = [
        'APP_ENV' => 'testing',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => $database,
        'DB_URL' => '',
        'QUEUE_CONNECTION' => 'sync',
        'MAIL_MAILER' => 'array',
        'CACHE_STORE' => 'array',
    ];

    [$first, $second] = Process::concurrently(function (Pool $pool) use ($script, $firstPayload, $secondPayload, $env) {
        $pool->path(base_path())
            ->env($env)
            ->command([PHP_BINARY, $script, $firstPayload]);

        $pool->path(base_path())
            ->env($env)
            ->command([PHP_BINARY, $script, $secondPayload]);
    });

    expect($first->successful())->toBeTrue("First process failed: {$first->errorOutput()}");
    expect($second->successful())->toBeTrue("Second process failed: {$second->errorOutput()}");

    $outcomes = [$first->output(), $second->output()];

    expect($outcomes)->toContain('created')
        ->and($outcomes)->toContain('rejected');

    DB::purge('sqlite');
    DB::reconnect('sqlite');

    expect(Product::query()->findOrFail($productId)->qty)->toBe(0);
    expect(Order::query()->count())->toBe(1);

    @unlink($database);
});
