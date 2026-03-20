<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotherboardSlot extends Model
{
    protected $table = 'motherboard_slots';
    public $timestamps = false;

    protected $fillable = ['product_id', 'slot_type', 'count'];

    public function motherboard()
    {
        return $this->belongsTo(Motherboard::class, 'product_id', 'product_id');
    }
}
