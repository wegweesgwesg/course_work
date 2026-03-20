<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateItem extends Model
{
    protected $primaryKey = 'template_item_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['template_item_id', 'template_id', 'slot_type', 'product_id', 'power_w'];

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'template_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
