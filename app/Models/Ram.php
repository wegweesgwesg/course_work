<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ram extends Model
{
    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['product_id', 'ram_type', 'size_gb', 'speed_mhz', 'power_w'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
