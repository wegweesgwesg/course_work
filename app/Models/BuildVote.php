<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildVote extends Model
{
    protected $fillable = ['published_build_id', 'user_id', 'vote'];

    public function build()
    {
        return $this->belongsTo(PublishedBuild::class, 'published_build_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
