<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'selling_price' => $this->selling_price,
            'tax_rate' => $this->tax_rate,
            'qty' => $this->qty,
            'min_qty_level' => $this->min_qty_level,
        ];
    }
}
