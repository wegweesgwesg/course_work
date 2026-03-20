<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\BuildItem;
use App\Models\Template;
use App\Models\TemplateItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BuildController extends Controller
{
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.slot' => 'required|string',
            'items.*.product_id' => 'required|string|exists:products,product_id',
            'items.*.quantity' => 'integer|min:1',
            'items.*.unit_price' => 'integer|min:0',
        ]);

        $totalPrice = 0;
        foreach ($validated['items'] as $item) {
            $totalPrice += ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1);
        }

        $build = Build::create([
            'user_id' => Auth::id(),
            'total_price' => $totalPrice,
        ]);

        foreach ($validated['items'] as $item) {
            BuildItem::create([
                'build_id' => $build->id,
                'slot_type' => $item['slot'],
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'] ?? 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'build_id' => $build->id,
            'total_price' => $totalPrice,
        ]);
    }

    public function exportBuild(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.slot' => 'required|string',
            'items.*.product_id' => 'required|string',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'integer|min:1',
            'items.*.unit_price' => 'integer|min:0',
        ]);

        $payload = [
            'build_id' => 'build-' . time(),
            'user_id' => Auth::id(),
            'items' => $validated['items'],
            'total_price' => array_sum(array_map(fn($i) => ($i['unit_price'] ?? 0) * ($i['quantity'] ?? 1), $validated['items'])),
        ];

        return response()->json($payload);
    }

    // Templates
    public function templates()
    {
        $templates = Template::with('items.product')->get()->map(function ($t) {
            return [
                'template_id' => $t->template_id,
                'name' => $t->name,
                'is_public' => $t->is_public,
                'author_user_id' => $t->author_user_id,
                'items' => $t->items->mapWithKeys(fn($item) => [
                    $item->slot_type => [
                        'product_id' => $item->product_id,
                        'name' => $item->product ? $item->product->name : $item->product_id,
                        'price' => $item->product ? $item->product->price : 0,
                        'main_image_path' => $item->product?->main_image_path ?? '',
                    ]
                ]),
            ];
        });

        return response()->json($templates);
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'items' => 'required|array',
            'items.*.slot_type' => 'required|string',
            'items.*.product_id' => 'required|string|exists:products,product_id',
            'is_public' => 'boolean',
        ]);

        $templateId = 'tpl-' . Str::random(8);

        $template = Template::create([
            'template_id' => $templateId,
            'name' => $validated['name'],
            'author_user_id' => Auth::id(),
            'is_public' => $validated['is_public'] ?? false,
        ]);

        foreach ($validated['items'] as $i => $item) {
            TemplateItem::create([
                'template_item_id' => 'titem-' . Str::random(8),
                'template_id' => $templateId,
                'slot_type' => $item['slot_type'],
                'product_id' => $item['product_id'],
            ]);
        }

        return response()->json(['success' => true, 'template_id' => $templateId], 201);
    }

    public function destroyTemplate(string $templateId)
    {
        $template = Template::findOrFail($templateId);
        $template->delete();
        return response()->json(['success' => true]);
    }

    public function updateTemplate(Request $request, string $templateId)
    {
        $template = Template::findOrFail($templateId);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'is_public' => 'sometimes|boolean',
            'items' => 'sometimes|array',
            'items.*.slot_type' => 'required_with:items|string',
            'items.*.product_id' => 'required_with:items|string|exists:products,product_id',
        ]);

        if (isset($validated['name'])) {
            $template->name = $validated['name'];
        }
        if (isset($validated['is_public'])) {
            $template->is_public = $validated['is_public'];
        }
        $template->save();

        if (isset($validated['items'])) {
            TemplateItem::where('template_id', $templateId)->delete();
            foreach ($validated['items'] as $item) {
                TemplateItem::create([
                    'template_item_id' => 'titem-' . Str::random(8),
                    'template_id' => $templateId,
                    'slot_type' => $item['slot_type'],
                    'product_id' => $item['product_id'],
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
}
