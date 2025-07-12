<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::create([
            'name' => 'Electronics',
            'description' => 'Electronic devices and accessories'
        ]);

        $smartphones = Category::create([
            'name' => 'Smartphones',
            'description' => 'Mobile phones and accessories',
            'parent_id' => $electronics->id
        ]);

        $laptops = Category::create([
            'name' => 'Laptops',
            'description' => 'Laptop computers and accessories',
            'parent_id' => $electronics->id
        ]);

        $clothing = Category::create([
            'name' => 'Clothing',
            'description' => 'Apparel and fashion items'
        ]);

        $mensClothing = Category::create([
            'name' => 'Men\'s Clothing',
            'description' => 'Clothing for men',
            'parent_id' => $clothing->id
        ]);

        $womensClothing = Category::create([
            'name' => 'Women\'s Clothing',
            'description' => 'Clothing for women',
            'parent_id' => $clothing->id
        ]);
    }
}