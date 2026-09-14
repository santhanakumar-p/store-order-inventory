<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * @param  array{customer: array{name: string, email: string}, items: list<array{product_id: int, qty: int}>}  $data
     */
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $customer = $this->findOrCreateCustomer(
                $data['customer']['email'],
                $data['customer']['name'],
            );

            $requestedItems = collect($data['items'])
                ->keyBy('product_id')
                ->sortKeys();

            $products = Product::query()
                ->whereIn('id', $requestedItems->keys())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lineItems = [];
            $orderSubtotal = 0.0;
            $orderTaxAmount = 0.0;
            $orderGrandTotal = 0.0;

            foreach ($requestedItems as $productId => $item) {
                /** @var Product|null $product */
                $product = $products->get($productId);

                if ($product === null) {
                    throw ValidationException::withMessages([
                        'items' => ["Product [{$productId}] was not found."],
                    ]);
                }

                $qty = (int) $item['qty'];

                if ($product->qty < $qty) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "Insufficient stock for product [{$product->sku}]. Available: {$product->qty}, requested: {$qty}.",
                        ],
                    ]);
                }

                $unitPrice = (float) $product->selling_price;
                $taxRate = (float) $product->tax_rate;
                $lineSubtotal = round($unitPrice * $qty, 2);
                $lineTaxAmount = round($lineSubtotal * $taxRate / 100, 2);
                $lineGrandTotal = round($lineSubtotal + $lineTaxAmount, 2);

                $lineItems[] = [
                    'product_id' => $product->id,
                    'unit_price' => $unitPrice,
                    'qty' => $qty,
                    'tax_rate' => $taxRate,
                    'line_subtotal' => $lineSubtotal,
                    'line_tax_amount' => $lineTaxAmount,
                    'line_grand_total' => $lineGrandTotal,
                ];

                $orderSubtotal = round($orderSubtotal + $lineSubtotal, 2);
                $orderTaxAmount = round($orderTaxAmount + $lineTaxAmount, 2);
                $orderGrandTotal = round($orderGrandTotal + $lineGrandTotal, 2);

                $product->decrement('qty', $qty);
            }

            $order = Order::query()->create([
                'customer_id' => $customer->id,
                'subtotal' => $orderSubtotal,
                'tax_amount' => $orderTaxAmount,
                'grand_total' => $orderGrandTotal,
            ]);

            $order->items()->createMany($lineItems);

            return $order->load(['customer', 'items.product']);
        });
    }

    private function findOrCreateCustomer(string $email, string $name): Customer
    {
        $customer = Customer::query()->where('email', $email)->first();

        if ($customer !== null) {
            return $customer;
        }

        return Customer::query()->create([
            'name' => $name,
            'email' => $email,
        ]);
    }
}
