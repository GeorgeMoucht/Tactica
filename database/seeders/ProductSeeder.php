<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::firstOrCreate(
            ['type' => 'registration'],
            [
                'name'              => 'Annual Registration',
                'price'             => 10.00,
                'billing_period'    => 'year',
                'duration_days'     => 365,
            ]
        );

        Product::firstOrCreate(
            ['type' => 'subscription'],
            [
                'name'           => 'Monthly Classes',
                'price'          => 40.00,
                'billing_period' => 'month',
                'duration_days'  => 30,
            ]
        );

        Product::firstOrCreate(
            ['type' => 'workshop'],
            [
                'name'           => 'One-time Workshop',
                'price'          => 15.00,
                'billing_period' => 'one_time',
                'duration_days'  => 1,
            ]
        );

        $this->command->info('Products seeded');
    }
}