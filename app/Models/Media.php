<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'alt_text',
        'caption',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function pages(): MorphToMany
    {
        return $this->morphedByMany(Page::class, 'mediable', 'media_attachments', 'media_id', 'mediable_id')
            ->withPivot(['role', 'sort_order']);
    }

    public function projects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'mediable', 'media_attachments', 'media_id', 'mediable_id')
            ->withPivot(['role', 'sort_order']);
    }

    public function blogPosts(): MorphToMany
    {
        return $this->morphedByMany(BlogPost::class, 'mediable', 'media_attachments', 'media_id', 'mediable_id')
            ->withPivot(['role', 'sort_order']);
    }
}
