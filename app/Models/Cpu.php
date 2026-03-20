<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cpu extends Model
{
    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'socket', 'tdp_w', 'max_mem_speed', 'brand',
        'cores', 'threads', 'base_clock', 'boost_clock',
        'integrated_graphics', 'lithography_nm', 'cache_mb', 'power_w',
    ];

    protected $casts = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
