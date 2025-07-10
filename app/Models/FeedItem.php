<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FeedItem extends Model
{
    protected $fillable = [
        'feed_id',
        'title',
        'description',
        'content',
        'url',
        'guid',
        'author',
        'published_at',
        'is_read',
        'is_starred',
        'image_url',
        'video_url',
        'duration',
        'metadata',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'metadata' => 'array',
    ];

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function markAsUnread(): void
    {
        $this->update(['is_read' => false]);
    }

    public function toggleStar(): void
    {
        $this->update(['is_starred' => !$this->is_starred]);
    }

    public function isVideo(): bool
    {
        return !empty($this->video_url);
    }

    public function isYouTubeVideo(): bool
    {
        return $this->isVideo() && (str_contains($this->video_url, 'youtube.com') || str_contains($this->video_url, 'youtu.be'));
    }

    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }
}
