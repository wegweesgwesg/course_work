<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cpu;
use App\Models\Motherboard;
use App\Models\Ram;
use App\Models\Gpu;
use App\Models\Storage;
use App\Models\Psu;
use App\Models\Cooler;
use App\Models\PcCase;
use App\Models\CaseRadiatorSupport;
use App\Models\ProductConnector;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function byCategory(Request $request, string $category)
    {
        $query = Product::where('category_id', $category)->where('is_active', true);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }

        // Spec filters: filter[key]=val1,val2
        if ($filters = $request->input('filter')) {
            $specTable = $this->specTable($category);
            if ($specTable && is_array($filters)) {
                $query->whereIn('products.product_id', function ($sub) use ($specTable, $filters) {
                    $sub->select('product_id')->from($specTable);
                    foreach ($filters as $key => $vals) {
                        $key = preg_replace('/[^a-z0-9_]/i', '', $key);
                        $values = array_map('trim', explode(',', $vals));
                        $sub->whereIn($key, $values);
                    }
                });
            }
        }

        $sort = $request->input('sort', 'name');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default => $query->orderBy('name'),
        };

        $products = $query->paginate(30);

        $products->getCollection()->transform(function ($product) {
            $product->specs_data = $this->getSpecs($product);
            return $product;
        });

        return response()->json($products);
    }

    public function filters(string $category)
    {
        $specTable = $this->specTable($category);
        if (!$specTable) {
            return response()->json([]);
        }

        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($specTable);
        $skip = ['product_id', 'id', 'created_at', 'updated_at', 'power_w'];
        $result = [];

        foreach ($columns as $col) {
            if (in_array($col, $skip)) continue;
            $values = \Illuminate\Support\Facades\DB::table($specTable)
                ->whereNotNull($col)
                ->where($col, '!=', '')
                ->distinct()
                ->pluck($col)
                ->sort()
                ->values()
                ->toArray();
            if (count($values) > 0) {
                $result[$col] = $values;
            }
        }

        return response()->json($result);
    }

    private function specTable(string $category): ?string
    {
        return match ($category) {
            'cpu' => 'cpus',
            'motherboard' => 'motherboards',
            'ram' => 'rams',
            'gpu' => 'gpus',
            'storage' => 'storages',
            'psu' => 'psus',
            'cooler' => 'coolers',
            'case' => 'cases',
            default => null,
        };
    }

    public function show(string $productId)
    {
        $product = Product::findOrFail($productId);
        $product->specs_data = $this->getSpecs($product);
        $product->connectors_data = ProductConnector::where('product_id', $productId)->get();

        if ($product->category_id === 'case') {
            $product->radiator_support = CaseRadiatorSupport::where('product_id', $productId)->pluck('size_mm');
        }

        return response()->json($product);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'category' => 'required|string|in:cpu,motherboard,ram,gpu,storage,psu,cooler,case',
        ]);

        $category = $request->input('category');
        $file = $request->file('image');
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);
        $extension = $file->getClientOriginalExtension();
        $finalName = $filename . '.' . $extension;

        $destDir = public_path('images/' . $category);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // If file exists, add suffix
        $counter = 1;
        while (file_exists($destDir . '/' . $finalName)) {
            $finalName = $filename . '_' . $counter . '.' . $extension;
            $counter++;
        }

        $file->move($destDir, $finalName);

        $relativePath = 'images/' . $category . '/' . $finalName;

        return response()->json(['path' => $relativePath]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|string|unique:products,product_id',
            'category_id' => 'required|string|exists:categories,category_id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'price' => 'required|integer|min:0',
            'stock_quantity' => 'integer|min:0',
            'description' => 'nullable|string',
            'main_image_path' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'specs' => 'nullable|array',
        ]);

        $product = Product::create([
            'product_id' => $validated['product_id'],
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? $validated['product_id'],
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'description' => $validated['description'] ?? '',
            'main_image_path' => $validated['main_image_path'] ?? '',
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (!empty($validated['specs'])) {
            $this->saveSpecs($product, $validated['specs']);
        }

        return response()->json($product, 201);
    }

    public function update(Request $request, string $productId)
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'nullable|string|max:255',
            'price' => 'sometimes|integer|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'description' => 'nullable|string',
            'main_image_path' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
            'specs' => 'nullable|array',
        ]);

        $product->update($validated);

        if (!empty($validated['specs'])) {
            $this->saveSpecs($product, $validated['specs']);
        }

        return response()->json($product);
    }

    public function destroy(string $productId)
    {
        $product = Product::findOrFail($productId);
        $product->delete();
        return response()->json(['message' => 'Удалено']);
    }

    private function getSpecs(Product $product): ?array
    {
        $specs = match ($product->category_id) {
            'cpu' => Cpu::find($product->product_id),
            'motherboard' => Motherboard::find($product->product_id),
            'ram' => Ram::find($product->product_id),
            'gpu' => Gpu::find($product->product_id),
            'storage' => Storage::find($product->product_id),
            'psu' => Psu::find($product->product_id),
            'cooler' => Cooler::find($product->product_id),
            'case' => PcCase::find($product->product_id),
            default => null,
        };
        return $specs ? $specs->toArray() : null;
    }

    private function saveSpecs(Product $product, array $specs): void
    {
        $specs['product_id'] = $product->product_id;
        match ($product->category_id) {
            'cpu' => Cpu::updateOrCreate(['product_id' => $product->product_id], $specs),
            'motherboard' => Motherboard::updateOrCreate(['product_id' => $product->product_id], $specs),
            'ram' => Ram::updateOrCreate(['product_id' => $product->product_id], $specs),
            'gpu' => Gpu::updateOrCreate(['product_id' => $product->product_id], $specs),
            'storage' => Storage::updateOrCreate(['product_id' => $product->product_id], $specs),
            'psu' => Psu::updateOrCreate(['product_id' => $product->product_id], $specs),
            'cooler' => Cooler::updateOrCreate(['product_id' => $product->product_id], $specs),
            'case' => PcCase::updateOrCreate(['product_id' => $product->product_id], $specs),
            default => null,
        };
    }
}
