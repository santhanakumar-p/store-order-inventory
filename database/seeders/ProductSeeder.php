<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Wireless Mouse',
                'sku' => 'ELEC-MOUSE-01',
                'selling_price' => 799.00,
                'tax_rate' => 18,
                'qty' => 45,
                'min_qty_level' => 10,
            ],
            [
                'name' => 'USB-C Charging Cable',
                'sku' => 'ELEC-CABLE-01',
                'selling_price' => 299.00,
                'tax_rate' => 18,
                'qty' => 120,
                'min_qty_level' => 20,
            ],
            [
                'name' => 'Notebook A5 (Pack of 5)',
                'sku' => 'STAT-NOTE-05',
                'selling_price' => 249.00,
                'tax_rate' => 12,
                'qty' => 80,
                'min_qty_level' => 15,
            ],
            [
                'name' => 'Ballpoint Pen Blue',
                'sku' => 'STAT-PEN-BL',
                'selling_price' => 25.00,
                'tax_rate' => 12,
                'qty' => 8,
                'min_qty_level' => 25,
            ],
            [
                'name' => 'Desk Organizer',
                'sku' => 'HOME-ORG-01',
                'selling_price' => 549.00,
                'tax_rate' => 18,
                'qty' => 30,
                'min_qty_level' => 8,
            ],
            [
                'name' => 'Cotton Tote Bag',
                'sku' => 'HOME-TOTE-01',
                'selling_price' => 199.00,
                'tax_rate' => 5,
                'qty' => 3,
                'min_qty_level' => 10,
            ],
            [
                'name' => 'Stainless Water Bottle 750ml',
                'sku' => 'HOME-BOTTLE-75',
                'selling_price' => 699.00,
                'tax_rate' => 12,
                'qty' => 22,
                'min_qty_level' => 5,
            ],
            [
                'name' => 'AA Batteries (Pack of 4)',
                'sku' => 'ELEC-BATT-AA4',
                'selling_price' => 180.00,
                'tax_rate' => 18,
                'qty' => 0,
                'min_qty_level' => 12,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                $product,
            );
        }
    }
}
