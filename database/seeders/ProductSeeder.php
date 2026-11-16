<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    
        $products = [
            ['name' => 'Classic T-Shirt', 'price' => 19.99, 'description' => 'Soft cotton tee in a relaxed fit.', 'image' => null],
            ['name' => 'Denim Jacket',    'price' => 59.99, 'description' => 'Timeless jacket with a washed finish.', 'image' => null],
            ['name' => 'Canvas Sneakers', 'price' => 39.50, 'description' => 'Lightweight everyday shoes.', 'image' => null],
            ['name' => 'Leather Wallet',  'price' => 24.00, 'description' => 'Slim bifold with card slots.', 'image' => null],
            ['name' => 'Baseball Cap',    'price' => 14.99, 'description' => 'Adjustable cap with a curved brim.', 'image' => null],
            ['name' => 'Backpack',        'price' => 45.00, 'description' => 'Water-resistant, fits a 15" laptop.', 'image' => null],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['name' => $product['name']], $product);
        }
    }
    }