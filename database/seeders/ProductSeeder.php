<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest iPhone with advanced features',
                'price' => 999.99,
                'quantity' => 50,
                'category_id' => $categories->where('name', 'Smartphones')->first()->id ?? 1
            ],
            [
                'name' => 'MacBook Pro 14"',
                'description' => 'High-performance laptop for professionals',
                'price' => 1999.99,
                'quantity' => 25,
                'category_id' => $categories->where('name', 'Laptops')->first()->id ?? 1
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'Android smartphone with great camera',
                'price' => 899.99,
                'quantity' => 30,
                'category_id' => $categories->where('name', 'Smartphones')->first()->id ?? 1
            ],
            [
                'name' => 'Dell XPS 13',
                'description' => 'Ultrabook with premium design',
                'price' => 1299.99,
                'quantity' => 20,
                'category_id' => $categories->where('name', 'Laptops')->first()->id ?? 1
            ],
            [
                'name' => 'Men\'s T-Shirt',
                'description' => 'Comfortable cotton t-shirt',
                'price' => 29.99,
                'quantity' => 100,
                'category_id' => $categories->where('name', 'Men\'s Clothing')->first()->id ?? 1
            ],
            [
                'name' => 'Women\'s Dress',
                'description' => 'Elegant evening dress',
                'price' => 89.99,
                'quantity' => 40,
                'category_id' => $categories->where('name', 'Women\'s Clothing')->first()->id ?? 1
            ]
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}