<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildItem extends Model
{
    public $timestamps = false;
    protected $fillable = ['build_id', 'slot_type', 'product_id', 'quantity', 'unit_price'];

    public function build()
    {
        return $this->belongsTo(Build::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
