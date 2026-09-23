<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'summary',
        'content',
        'slug',
        'status',
        'sort_order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function mediaAttachments(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable', 'media_attachments', 'mediable_id', 'media_id')
            ->withPivot(['role', 'sort_order']);
    }
}
