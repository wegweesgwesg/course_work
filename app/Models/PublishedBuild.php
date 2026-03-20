<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublishedBuild extends Model
{
    protected $fillable = ['user_id', 'name', 'description', 'build_data', 'total_price'];

    protected $casts = [
        'build_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votes()
    {
        return $this->hasMany(BuildVote::class, 'published_build_id');
    }

    public function comments()
    {
        return $this->hasMany(BuildComment::class, 'published_build_id');
    }

    public function getScoreAttribute(): int
    {
        return $this->votes()->sum('vote');
    }
}
