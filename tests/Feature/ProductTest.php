<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_product(): void
    {
        $category = Category::factory()->create();
        
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'quantity' => 10,
            'category_id' => $category->id
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(201)
                ->assertJson([
                    'name' => 'Test Product',
                    'description' => 'Test Description',
                    'price' => '99.99',
                    'quantity' => 10
                ]);

        $this->assertDatabaseHas('products', $productData);
    }

    public function test_can_search_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'name' => 'iPhone 15',
            'category_id' => $category->id
        ]);
        Product::factory()->create([
            'name' => 'Samsung Galaxy',
            'category_id' => $category->id
        ]);

        $response = $this->getJson('/api/products?search=iPhone');

        $response->assertStatus(200)
                ->assertJsonPath('data.0.name', 'iPhone 15');
    }

    public function test_can_filter_by_price_range(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'name' => 'Cheap Product',
            'price' => 10.00,
            'category_id' => $category->id
        ]);
        Product::factory()->create([
            'name' => 'Expensive Product',
            'price' => 100.00,
            'category_id' => $category->id
        ]);

        $response = $this->getJson('/api/products?min_price=50&max_price=150');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Expensive Product', $data[0]['name']);
    }

    public function test_can_filter_by_availability(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'name' => 'Available Product',
            'quantity' => 5,
            'category_id' => $category->id
        ]);
        Product::factory()->create([
            'name' => 'Out of Stock Product',
            'quantity' => 0,
            'category_id' => $category->id
        ]);

        $response = $this->getJson('/api/products?availability=available');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Available Product', $data[0]['name']);
    }

    public function test_can_update_product_quantity(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'quantity' => 10,
            'category_id' => $category->id
        ]);

        $response = $this->patchJson("/api/products/{$product->id}/quantity", [
            'quantity' => 5,
            'operation' => 'increment'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 15
        ]);
    }

    public function test_quantity_update_prevents_negative_values(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'quantity' => 5,
            'category_id' => $category->id
        ]);

        $response = $this->patchJson("/api/products/{$product->id}/quantity", [
            'quantity' => 10,
            'operation' => 'decrement'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 0
        ]);
    }

    public function test_can_export_products_csv(): void
    {
        $response = $this->postJson('/api/products/export-csv');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'filename'
                ]);
    }
}