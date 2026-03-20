<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Psu extends Model
{
    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['product_id', 'power_w'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function connectors()
    {
        return $this->hasMany(ProductConnector::class, 'product_id', 'product_id');
    }
}
