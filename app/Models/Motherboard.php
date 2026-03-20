<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motherboard extends Model
{
    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'socket', 'form_factor', 'ram_slots', 'max_ram',
        'ram_speed_max', 'm2_slots', 'pcie_version', 'cpu_fan_headers',
        'sata_ports', 'chipset', 'brand', 'power_w',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function slots()
    {
        return $this->hasMany(MotherboardSlot::class, 'product_id', 'product_id');
    }
}
