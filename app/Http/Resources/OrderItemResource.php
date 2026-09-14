<?php

namespace App\Http\Resources;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */
class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->whenLoaded('product', fn () => $this->product?->name),
            'product_sku' => $this->whenLoaded('product', fn () => $this->product?->sku),
            'unit_price' => $this->unit_price,
            'qty' => $this->qty,
            'tax_rate' => $this->tax_rate,
            'line_subtotal' => $this->line_subtotal,
            'line_tax_amount' => $this->line_tax_amount,
            'line_grand_total' => $this->line_grand_total,
        ];
    }
}
