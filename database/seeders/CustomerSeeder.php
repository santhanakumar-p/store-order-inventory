<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
            ],
            [
                'name' => 'Rahul Sharma',
                'email' => 'rahul.sharma@example.com',
            ],
            [
                'name' => 'Priya Patel',
                'email' => 'priya.patel@example.com',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::query()->updateOrCreate(
                ['email' => $customer['email']],
                $customer,
            );
        }
    }
}
