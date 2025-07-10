<?php

namespace App\Jobs;

use App\Models\Feed;
use App\Services\RssFeedService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Exception;

class FetchFeedJob implements ShouldQueue
{
    use Queueable;

    public Feed $feed;
    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
    }

    /**
     * Execute the job.
     */
    public function handle(RssFeedService $rssFeedService): void
    {
        try {
            if (!$this->feed->shouldFetch()) {
                Log::info("Skipping feed {$this->feed->id} - not ready for fetch");
                return;
            }

            Log::info("Fetching feed {$this->feed->id}: {$this->feed->title}");
            
            $items = $rssFeedService->fetchFeed($this->feed);
            $newItemsCount = $rssFeedService->storeFeedItems($this->feed, $items);
            
            Log::info("Feed {$this->feed->id} fetched successfully. New items: {$newItemsCount}");
            
        } catch (Exception $e) {
            Log::error("Error fetching feed {$this->feed->id}: {$e->getMessage()}", [
                'feed_id' => $this->feed->id,
                'feed_url' => $this->feed->feed_url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("Feed fetch job failed for feed {$this->feed->id}", [
            'feed_id' => $this->feed->id,
            'feed_url' => $this->feed->feed_url,
            'error' => $exception->getMessage(),
        ]);
        
        // Optionally disable the feed after multiple failures
        if ($this->attempts() >= $this->tries) {
            $this->feed->update(['is_active' => false]);
            Log::warning("Disabled feed {$this->feed->id} after {$this->tries} failed attempts");
        }
    }
}
