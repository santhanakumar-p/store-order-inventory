<?php

use App\Mail\OrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(LazilyRefreshDatabase::class);

it('creates an order, customer, reduces stock, and queues confirmation mail', function () {
    Mail::fake();

    $product = Product::factory()->create([
        'selling_price' => 100.00,
        'tax_rate' => 18.00,
        'qty' => 10,
    ]);

    $response = $this->postJson('/api/orders', [
        'customer' => [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ],
        'items' => [
            [
                'product_id' => $product->id,
                'qty' => 2,
            ],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.subtotal', '200.00')
        ->assertJsonPath('data.tax_amount', '36.00')
        ->assertJsonPath('data.grand_total', '236.00')
        ->assertJsonPath('data.customer.email', 'jane@example.com')
        ->assertJsonPath('data.items.0.unit_price', '100.00')
        ->assertJsonPath('data.items.0.tax_rate', '18.00')
        ->assertJsonPath('data.items.0.line_subtotal', '200.00')
        ->assertJsonPath('data.items.0.line_tax_amount', '36.00')
        ->assertJsonPath('data.items.0.line_grand_total', '236.00');

    $this->assertDatabaseHas('customers', [
        'email' => 'jane@example.com',
        'name' => 'Jane Doe',
    ]);

    $this->assertDatabaseHas('orders', [
        'subtotal' => 200.00,
        'tax_amount' => 36.00,
        'grand_total' => 236.00,
    ]);

    $this->assertDatabaseHas('order_items', [
        'product_id' => $product->id,
        'qty' => 2,
        'unit_price' => 100.00,
        'tax_rate' => 18.00,
    ]);

    expect($product->fresh()->qty)->toBe(8);

    Mail::assertQueued(OrderConfirmation::class, function (OrderConfirmation $mail): bool {
        return $mail->hasTo('jane@example.com');
    });
});

it('reuses an existing customer by email and keeps the stored name', function () {
    Mail::fake();

    $customer = Customer::factory()->create([
        'name' => 'Original Name',
        'email' => 'existing@example.com',
    ]);

    $product = Product::factory()->create([
        'selling_price' => 50.00,
        'tax_rate' => 0,
        'qty' => 5,
    ]);

    $response = $this->postJson('/api/orders', [
        'customer' => [
            'name' => 'Different Name',
            'email' => 'existing@example.com',
        ],
        'items' => [
            [
                'product_id' => $product->id,
                'qty' => 1,
            ],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.customer.id', $customer->id)
        ->assertJsonPath('data.customer.name', 'Original Name');

    expect(Customer::query()->where('email', 'existing@example.com')->count())->toBe(1);
    expect($customer->fresh()->name)->toBe('Original Name');
});

it('returns 422 when product ids are duplicated in the request', function () {
    Mail::fake();

    $product = Product::factory()->create(['qty' => 10]);

    $response = $this->postJson('/api/orders', [
        'customer' => [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ],
        'items' => [
            ['product_id' => $product->id, 'qty' => 1],
            ['product_id' => $product->id, 'qty' => 2],
        ],
    ]);

    $response->assertUnprocessable()
        ->assertInvalid(['items.0.product_id' => 'Duplicate product_id values are not allowed in a single order.']);

    expect(Order::query()->count())->toBe(0);
    expect($product->fresh()->qty)->toBe(10);
    Mail::assertNothingOutgoing();
});

it('returns 422 when requested quantity exceeds available stock', function () {
    Mail::fake();

    $product = Product::factory()->create([
        'sku' => 'LOW-STOCK-1',
        'qty' => 1,
    ]);

    $response = $this->postJson('/api/orders', [
        'customer' => [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ],
        'items' => [
            ['product_id' => $product->id, 'qty' => 2],
        ],
    ]);

    $response->assertUnprocessable()
        ->assertInvalid(['items']);

    expect($response->json('errors.items.0'))->toContain('Insufficient stock');
    expect(Order::query()->count())->toBe(0);
    expect($product->fresh()->qty)->toBe(1);
    Mail::assertNothingOutgoing();
});
