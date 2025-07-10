<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Feed extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'url',
        'feed_url',
        'description',
        'site_url',
        'language',
        'image_url',
        'last_fetched_at',
        'is_active',
        'fetch_interval',
        'metadata',
    ];

    protected $casts = [
        'last_fetched_at' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeedItem::class);
    }

    public function recentItems(): HasMany
    {
        return $this->items()->orderBy('published_at', 'desc')->limit(10);
    }

    public function unreadItems(): HasMany
    {
        return $this->items()->where('is_read', false);
    }

    public function starredItems(): HasMany
    {
        return $this->items()->where('is_starred', true);
    }

    public function shouldFetch(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->last_fetched_at) {
            return true;
        }

        return $this->last_fetched_at->addSeconds($this->fetch_interval)->isPast();
    }

    public function getUnreadCountAttribute(): int
    {
        return $this->unreadItems()->count();
    }

    public function isYouTubeFeed(): bool
    {
        return str_contains($this->feed_url, 'youtube.com') || str_contains($this->feed_url, 'youtu.be');
    }
}
