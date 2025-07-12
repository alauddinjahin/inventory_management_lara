<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::with(['parent', 'children'])->get();
        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $category = Category::create($validated);
        $category->load(['parent', 'children']);

        return response()->json($category, 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load(['parent', 'children.allChildren', 'products']);
        return response()->json($category);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                Rule::notIn([$category->id]) // Prevent self-referencing
            ]
        ]);

        // Prevent circular references
        if ($validated['parent_id'] && $this->wouldCreateCircularReference($category, $validated['parent_id'])) {
            return response()->json(['error' => 'Cannot create circular reference'], 400);
        }

        try {
            $category->update($validated);
            $category->load(['parent', 'children']);
            
            return response()->json($category);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update category'], 500);
        }
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }

    private function wouldCreateCircularReference(Category $category, $parentId): bool
    {
        $parent = Category::find($parentId);
        
        while ($parent) {
            if ($parent->id === $category->id) {
                return true;
            }
            $parent = $parent->parent;
        }
        
        return false;
    }
}