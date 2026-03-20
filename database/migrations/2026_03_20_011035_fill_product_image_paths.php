<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $products = DB::table('products')->whereNull('main_image_path')
            ->orWhere('main_image_path', '')->get(['product_id', 'category_id']);

        foreach ($products as $product) {
            $imagePath = 'images/' . $product->category_id . '/' . $product->product_id . '.png';
            $fullPath = public_path($imagePath);

            if (file_exists($fullPath)) {
                DB::table('products')
                    ->where('product_id', $product->product_id)
                    ->update(['main_image_path' => $imagePath]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
