<?php

namespace App\Console\Commands;

use App\Jobs\FetchFeedJob;
use App\Models\Feed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchRssFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rss:fetch {--feed-id= : Fetch specific feed by ID} {--force : Force fetch even if not due}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch RSS feeds and update items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $feedId = $this->option('feed-id');
        $force = $this->option('force');
        
        if ($feedId) {
            $feed = Feed::find($feedId);
            if (!$feed) {
                $this->error("Feed with ID {$feedId} not found.");
                return 1;
            }
            
            $this->info("Fetching feed: {$feed->title}");
            FetchFeedJob::dispatch($feed);
            $this->info("Feed job dispatched.");
            return 0;
        }
        
        $query = Feed::where('is_active', true);
        
        if (!$force) {
            // Only fetch feeds that are due for update
            $query->where(function ($q) {
                $q->whereNull('last_fetched_at')
                  ->orWhereRaw('last_fetched_at + INTERVAL fetch_interval SECOND < NOW()');
            });
        }
        
        $feeds = $query->get();
        
        if ($feeds->isEmpty()) {
            $this->info('No feeds need updating at this time.');
            return 0;
        }
        
        $this->info("Found {$feeds->count()} feeds to fetch.");
        
        $bar = $this->output->createProgressBar($feeds->count());
        $bar->start();
        
        foreach ($feeds as $feed) {
            FetchFeedJob::dispatch($feed);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Dispatched {$feeds->count()} feed fetch jobs.");
        
        Log::info("RSS feed fetch command completed", [
            'feeds_processed' => $feeds->count(),
            'force' => $force,
        ]);
        
        return 0;
    }
}
