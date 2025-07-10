<?php

namespace App\Services;

use App\Models\Feed;
use App\Models\FeedItem;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RssFeedService
{
    protected Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'RSS Reader Bot/1.0',
            ],
        ]);
    }

    /**
     * Discover RSS feed URL from a given URL
     */
    public function discoverFeedUrl(string $url): ?string
    {
        // Handle YouTube channel URLs
        if ($this->isYouTubeUrl($url)) {
            return $this->getYouTubeFeedUrl($url);
        }

        try {
            $response = $this->httpClient->get($url);
            $html = $response->getBody()->getContents();

            // Look for RSS feed links in HTML
            if (preg_match('/<link[^>]*type=["\']application\/rss\+xml["\'][^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
                return $this->resolveUrl($matches[1], $url);
            }

            if (preg_match('/<link[^>]*href=["\']([^"\']+)["\'][^>]*type=["\']application\/rss\+xml["\'][^>]*>/i', $html, $matches)) {
                return $this->resolveUrl($matches[1], $url);
            }

            // Look for Atom feed links
            if (preg_match('/<link[^>]*type=["\']application\/atom\+xml["\'][^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
                return $this->resolveUrl($matches[1], $url);
            }

            // Try common RSS paths
            $commonPaths = ['/rss', '/rss.xml', '/feed', '/feed.xml', '/atom.xml'];
            $baseUrl = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);

            foreach ($commonPaths as $path) {
                $feedUrl = $baseUrl . $path;
                if ($this->validateFeedUrl($feedUrl)) {
                    return $feedUrl;
                }
            }

        } catch (Exception $e) {
            Log::error('Error discovering feed URL: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Check if a URL is a YouTube URL
     */
    protected function isYouTubeUrl(string $url): bool
    {
        return str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be');
    }

    /**
     * Convert YouTube channel URL to RSS feed URL
     */
    protected function getYouTubeFeedUrl(string $url): ?string
    {
        // Extract channel ID from various YouTube URL formats
        $patterns = [
            '/youtube\.com\/channel\/([a-zA-Z0-9_-]+)/' => 'direct',
            '/youtube\.com\/c\/([a-zA-Z0-9_-]+)/' => 'resolve',
            '/youtube\.com\/user\/([a-zA-Z0-9_-]+)/' => 'resolve',
            '/youtube\.com\/@([a-zA-Z0-9_-]+)/' => 'resolve',
        ];

        foreach ($patterns as $pattern => $type) {
            if (preg_match($pattern, $url, $matches)) {
                if ($type === 'direct') {
                    // Direct channel ID
                    return "https://www.youtube.com/feeds/videos.xml?channel_id={$matches[1]}";
                } else {
                    // Need to resolve username/handle to channel ID
                    $channelId = $this->resolveYouTubeChannelId($url, $matches[1]);
                    if ($channelId) {
                        return "https://www.youtube.com/feeds/videos.xml?channel_id={$channelId}";
                    }
                }
            }
        }

        return null;
    }

    /**
     * Resolve YouTube @username to channel ID
     */
    protected function resolveYouTubeChannelId(string $channelUrl, string $username): ?string
    {
        try {
            // Fetch the YouTube channel page
            $response = $this->httpClient->get($channelUrl);
            $html = $response->getBody()->getContents();
            
            // Look for channel ID in various places in the HTML
            $patterns = [
                '/"channelId":"([a-zA-Z0-9_-]+)"/',
                '/\/channel\/([a-zA-Z0-9_-]+)/',
                '/data-channel-external-id="([a-zA-Z0-9_-]+)"/',
                '/"externalId":"([a-zA-Z0-9_-]+)"/',
            ];
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    $channelId = $matches[1];
                    // Validate that it looks like a proper channel ID (starts with UC)
                    if (str_starts_with($channelId, 'UC') && strlen($channelId) === 24) {
                        Log::info("Resolved YouTube channel ID for {$username}: {$channelId}");
                        return $channelId;
                    }
                }
            }
            
            Log::warning("Could not resolve YouTube channel ID for: {$channelUrl}");
            return null;
            
        } catch (Exception $e) {
            Log::error("Error resolving YouTube channel ID for {$channelUrl}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Validate if a URL is a valid RSS feed
     */
    public function validateFeedUrl(string $url): bool
    {
        try {
            $response = $this->httpClient->get($url);
            $content = $response->getBody()->getContents();

            // Check if it's valid XML with RSS or Atom elements
            $xml = simplexml_load_string($content);
            return $xml !== false && (
                $xml->getName() === 'rss' ||
                $xml->getName() === 'feed' ||
                isset($xml->channel)
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Fetch and parse RSS feed
     */
    public function fetchFeed(Feed $feed): array
    {
        try {
            $response = $this->httpClient->get($feed->feed_url);
            $content = $response->getBody()->getContents();

            $xml = simplexml_load_string($content);
            if (!$xml) {
                throw new Exception('Invalid XML content');
            }

            $items = [];

            // Handle RSS format
            if ($xml->getName() === 'rss' && isset($xml->channel)) {
                $items = $this->parseRssItems($xml->channel->item ?? []);
            }
            // Handle Atom format
            elseif ($xml->getName() === 'feed') {
                $items = $this->parseAtomItems($xml->entry ?? []);
            }

            // Update feed metadata
            $this->updateFeedMetadata($feed, $xml);

            return $items;

        } catch (Exception $e) {
            Log::error("Error fetching feed {$feed->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse RSS items
     */
    protected function parseRssItems($items): array
    {
        $parsedItems = [];

        foreach ($items as $item) {
            $parsedItems[] = [
                'title' => (string) $item->title,
                'description' => (string) ($item->description ?? ''),
                'content' => (string) ($item->children('content', true)->encoded ?? ''),
                'url' => (string) $item->link,
                'guid' => (string) ($item->guid ?? $item->link),
                'author' => (string) ($item->author ?? $item->children('dc', true)->creator ?? ''),
                'published_at' => $this->parseDate((string) ($item->pubDate ?? $item->children('dc', true)->date)),
                'image_url' => $this->extractImageUrl($item),
                'video_url' => $this->extractVideoUrl($item),
                'duration' => $this->extractDuration($item),
            ];
        }

        return $parsedItems;
    }

    /**
     * Parse Atom items
     */
    protected function parseAtomItems($entries): array
    {
        $parsedItems = [];

        foreach ($entries as $entry) {
            $parsedItems[] = [
                'title' => (string) $entry->title,
                'description' => (string) ($entry->summary ?? ''),
                'content' => (string) ($entry->content ?? ''),
                'url' => (string) $entry->link['href'],
                'guid' => (string) $entry->id,
                'author' => (string) ($entry->author->name ?? ''),
                'published_at' => $this->parseDate((string) ($entry->published ?? $entry->updated)),
                'image_url' => $this->extractImageUrl($entry),
                'video_url' => $this->extractVideoUrl($entry),
                'duration' => $this->extractDuration($entry),
            ];
        }

        return $parsedItems;
    }

    /**
     * Extract image URL from feed item
     */
    protected function extractImageUrl($item): ?string
    {
        // Check for media:thumbnail (YouTube)
        $media = $item->children('media', true);
        if (isset($media->group->thumbnail)) {
            return (string) $media->group->thumbnail['url'];
        }

        // Check for enclosure
        if (isset($item->enclosure) && str_contains((string) $item->enclosure['type'], 'image')) {
            return (string) $item->enclosure['url'];
        }

        return null;
    }

    /**
     * Extract video URL from feed item
     */
    protected function extractVideoUrl($item): ?string
    {
        // Check for media:content (YouTube)
        $media = $item->children('media', true);
        if (isset($media->group->content)) {
            $content = $media->group->content;
            if (str_contains((string) $content['type'], 'video')) {
                return (string) $content['url'];
            }
        }

        // For YouTube feeds, use the link
        $link = (string) ($item->link ?? $item->link['href'] ?? '');
        if (str_contains($link, 'youtube.com/watch') || str_contains($link, 'youtu.be/')) {
            return $link;
        }

        return null;
    }

    /**
     * Extract video duration from feed item
     */
    protected function extractDuration($item): ?int
    {
        $media = $item->children('media', true);
        if (isset($media->group->content)) {
            $duration = (string) $media->group->content['duration'];
            return $duration ? (int) $duration : null;
        }

        return null;
    }

    /**
     * Parse date string to Carbon instance
     */
    protected function parseDate(?string $date): ?Carbon
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Update feed metadata from XML
     */
    protected function updateFeedMetadata(Feed $feed, $xml): void
    {
        $updates = ['last_fetched_at' => now()];

        if ($xml->getName() === 'rss' && isset($xml->channel)) {
            $channel = $xml->channel;
            $updates['title'] = (string) $channel->title;
            $updates['description'] = (string) $channel->description;
            $updates['site_url'] = (string) $channel->link;
            $updates['language'] = (string) $channel->language;

            if (isset($channel->image->url)) {
                $updates['image_url'] = (string) $channel->image->url;
            }
        } elseif ($xml->getName() === 'feed') {
            $updates['title'] = (string) $xml->title;
            $updates['description'] = (string) $xml->subtitle;
            $updates['site_url'] = (string) $xml->link['href'];
        }

        $feed->update($updates);
    }

    /**
     * Store feed items in database
     */
    public function storeFeedItems(Feed $feed, array $items): int
    {
        $newItemsCount = 0;

        foreach ($items as $itemData) {
            $existing = FeedItem::where('feed_id', $feed->id)
                ->where('guid', $itemData['guid'])
                ->first();

            if (!$existing) {
                FeedItem::create(array_merge($itemData, [
                    'feed_id' => $feed->id,
                ]));
                $newItemsCount++;
            }
        }

        return $newItemsCount;
    }

    /**
     * Resolve relative URL to absolute URL
     */
    protected function resolveUrl(string $url, string $baseUrl): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }

        $base = parse_url($baseUrl);
        $scheme = $base['scheme'] ?? 'https';
        $host = $base['host'] ?? '';

        if (str_starts_with($url, '/')) {
            return "{$scheme}://{$host}{$url}";
        }

        return "{$scheme}://{$host}/" . ltrim($url, '/');
    }
}
