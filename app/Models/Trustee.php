<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trustee extends Model
{
    protected $fillable = [
        'name', 'slug', 'designation', 'content', 'image_media_id',
        'facebook_url', 'twitter_url', 'linkedin_url', 'instagram_url', 'other_profile_url',
        'status', 'sort_order', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }
}
