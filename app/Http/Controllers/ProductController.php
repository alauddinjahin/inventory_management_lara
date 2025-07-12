<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Jobs\ExportProductsJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category');

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Price range filter
        if ($request->has('min_price') || $request->has('max_price')) {
            $query->filterByPrice($request->min_price, $request->max_price);
        }

        // Availability filter
        if ($request->has('availability')) {
            if ($request->availability === 'available') {
                $query->available();
            } elseif ($request->availability === 'out_of_stock') {
                $query->outOfStock();
            }
        }

        // Category filter
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(15);

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $product = Product::create($validated);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $product->addMediaFromRequest('images')
                       ->each(function ($fileAdder) {
                           $fileAdder->toMediaCollection('product_images');
                       });
            }
        }

        $product->load('category');
        return response()->json($product, 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'media']);
        return response()->json($product);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $product->update($validated);

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $product->addMediaFromRequest('images')
                       ->each(function ($fileAdder) {
                           $fileAdder->toMediaCollection('product_images');
                       });
            }
        }

        $product->load('category');
        return response()->json($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function updateQuantity(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'operation' => 'required|in:set,increment,decrement'
        ]);

        // Use database-level locking to prevent race conditions
        DB::transaction(function () use ($product, $validated) {
            $product = Product::lockForUpdate()->find($product->id);
            
            switch ($validated['operation']) {
                case 'set':
                    $product->quantity = $validated['quantity'];
                    break;
                case 'increment':
                    $product->quantity += $validated['quantity'];
                    break;
                case 'decrement':
                    $product->quantity = max(0, $product->quantity - $validated['quantity']);
                    break;
            }
            
            $product->save();
        });

        return response()->json(['message' => 'Quantity updated successfully', 'product' => $product]);
    }

    public function exportCsv(): JsonResponse
    {
        $filename = 'products_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        ExportProductsJob::dispatch($filename);

        return response()->json([
            'message' => 'CSV export started. You will be notified when it\'s ready.',
            'filename' => $filename
        ]);
    }

    public function downloadCsv(string $filename)
    {
        $filePath = 'exports/' . $filename;
        
        if (!Storage::exists($filePath)) {
            abort(404, 'File not found');
        }

        return Storage::download($filePath);
    }
}