<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cooler extends Model
{
    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['product_id', 'cooler_height_mm', 'connector_pin_count', 'radiator_size_mm', 'power_w'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
