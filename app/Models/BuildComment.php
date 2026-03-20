<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildComment extends Model
{
    protected $fillable = ['published_build_id', 'user_id', 'text'];

    public function build()
    {
        return $this->belongsTo(PublishedBuild::class, 'published_build_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
