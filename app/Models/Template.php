<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $primaryKey = 'template_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['template_id', 'name', 'author_user_id', 'is_public', 'power_w'];

    protected $casts = ['is_public' => 'boolean'];

    public function items()
    {
        return $this->hasMany(TemplateItem::class, 'template_id', 'template_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
