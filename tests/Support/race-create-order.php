<?php

use App\Services\OrderService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

config([
    'database.connections.sqlite.busy_timeout' => 5000,
    'database.connections.sqlite.transaction_mode' => 'IMMEDIATE',
]);

DB::purge('sqlite');
DB::reconnect('sqlite');

/** @var array{customer: array{name: string, email: string}, items: list<array{product_id: int, qty: int}>} $payload */
$payload = json_decode($argv[1] ?? '', true, 512, JSON_THROW_ON_ERROR);

$attempts = 0;

while (true) {
    try {
        app(OrderService::class)->create($payload);
        echo 'created';
        exit(0);
    } catch (ValidationException) {
        echo 'rejected';
        exit(0);
    } catch (QueryException $exception) {
        if (++$attempts >= 15 || ! str_contains($exception->getMessage(), 'database is locked')) {
            fwrite(STDERR, $exception->getMessage());
            exit(1);
        }

        usleep(100_000);
    }
}
