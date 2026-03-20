<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'product_id', 'category_id', 'sku', 'name', 'price',
        'stock_quantity', 'description', 'main_image_path', 'is_active', 'power_w',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function cpu()
    {
        return $this->hasOne(Cpu::class, 'product_id', 'product_id');
    }

    public function motherboard()
    {
        return $this->hasOne(Motherboard::class, 'product_id', 'product_id');
    }

    public function ram()
    {
        return $this->hasOne(Ram::class, 'product_id', 'product_id');
    }

    public function gpu()
    {
        return $this->hasOne(Gpu::class, 'product_id', 'product_id');
    }

    public function storage()
    {
        return $this->hasOne(Storage::class, 'product_id', 'product_id');
    }

    public function psu()
    {
        return $this->hasOne(Psu::class, 'product_id', 'product_id');
    }

    public function cooler()
    {
        return $this->hasOne(Cooler::class, 'product_id', 'product_id');
    }

    public function pcCase()
    {
        return $this->hasOne(PcCase::class, 'product_id', 'product_id');
    }

    public function connectors()
    {
        return $this->hasMany(ProductConnector::class, 'product_id', 'product_id');
    }

    public function specs()
    {
        return match ($this->category_id) {
            'cpu' => $this->cpu(),
            'motherboard' => $this->motherboard(),
            'ram' => $this->ram(),
            'gpu' => $this->gpu(),
            'storage' => $this->storage(),
            'psu' => $this->psu(),
            'cooler' => $this->cooler(),
            'case' => $this->pcCase(),
            default => null,
        };
    }

    public function getSpecsAttribute()
    {
        $relation = $this->specs();
        return $relation ? $relation->first() : null;
    }
}
