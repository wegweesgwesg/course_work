<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseRadiatorSupport extends Model
{
    protected $table = 'case_radiator_supports';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id', 'product_id', 'size_mm', 'power_w'];

    public function pcCase()
    {
        return $this->belongsTo(PcCase::class, 'product_id', 'product_id');
    }
}
