<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcCase extends Model
{
    protected $table = 'cases';
    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'form_factor', 'max_gpu_length_mm', 'max_cooler_height_mm',
        'm2_slots', 'drive_bays', 'front_usb_c', 'audio_header', 'power_w',
    ];

    protected $casts = [
        'form_factor' => 'array',
        'front_usb_c' => 'boolean',
        'audio_header' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function radiatorSupports()
    {
        return $this->hasMany(CaseRadiatorSupport::class, 'product_id', 'product_id');
    }
}
