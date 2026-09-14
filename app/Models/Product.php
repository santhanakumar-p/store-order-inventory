<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'sku',
        'selling_price',
        'tax_rate',
        'qty',
        'min_qty_level',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'qty' => 'integer',
            'min_qty_level' => 'integer',
        ];
    }

    /**
     * @param  Builder<Product>  $query
     */
    #[Scope]
    protected function lowStock(Builder $query): void
    {
        $query->whereColumn('qty', '<=', 'min_qty_level');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
