<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_category(): void
    {
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description'
        ];

        $response = $this->postJson('/api/categories', $categoryData);

        $response->assertStatus(201)
                ->assertJson([
                    'name' => 'Test Category',
                    'description' => 'Test Description'
                ]);

        $this->assertDatabaseHas('categories', $categoryData);
    }

    public function test_can_create_subcategory(): void
    {
        $parentCategory = Category::factory()->create();
        
        $subcategoryData = [
            'name' => 'Subcategory',
            'description' => 'Sub Description',
            'parent_id' => $parentCategory->id
        ];

        $response = $this->postJson('/api/categories', $subcategoryData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', $subcategoryData);
    }

    public function test_can_list_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => ['id', 'name', 'description', 'parent_id', 'created_at', 'updated_at']
                ]);
    }


    public function test_can_update_category(): void
    {
        $category = Category::factory()->create();
        
        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ];

        $response = $this->putJson("/api/categories/{$category->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'name' => 'Updated Category',
                    'description' => 'Updated Description'
                ]);

        $this->assertDatabaseHas('categories', array_merge(['id' => $category->id], $updateData));
    }


    public function test_can_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_prevents_circular_reference(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $response = $this->putJson("/api/categories/{$parent->id}", [
            'name' => $parent->name,
            'description' => $parent->description,
            'parent_id' => $child->id
        ]);

        $response->assertStatus(400);
    }
}