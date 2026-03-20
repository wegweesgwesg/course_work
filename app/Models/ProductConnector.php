<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductConnector extends Model
{
    protected $primaryKey = 'connector_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['connector_id', 'product_id', 'connector_type', 'power_w'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
