<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function handle(): void
    {
        $products = Product::with('category')->get();
        
        $csvData = [];
        $csvData[] = ['ID', 'Name', 'Description', 'Price', 'Quantity', 'Category', 'Created At'];

        foreach ($products as $product) {
            $csvData[] = [
                $product->id,
                $product->name,
                $product->description,
                $product->price,
                $product->quantity,
                $product->category->name ?? 'N/A',
                $product->created_at->format('Y-m-d H:i:s')
            ];
        }

        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= implode(',', array_map(function ($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }

        Storage::put('exports/' . $this->filename, $csvContent);
    }
}