<?php

namespace App\Livewire;

use App\Models\Feed;
use App\Services\RssFeedService;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class FeedManager extends Component
{
    #[Validate('required|url|max:500')]
    public string $url = '';
    
    public bool $showAddForm = false;
    public string $message = '';
    public string $messageType = '';
    
    protected RssFeedService $rssFeedService;
    
    public function boot(RssFeedService $rssFeedService)
    {
        $this->rssFeedService = $rssFeedService;
    }
    
    public function toggleAddForm()
    {
        $this->showAddForm = !$this->showAddForm;
        $this->url = '';
        $this->message = '';
    }
    
    public function addFeed()
    {
        $this->validate();
        
        try {
            // Discover RSS feed URL
            $feedUrl = $this->rssFeedService->discoverFeedUrl($this->url);
            
            if (!$feedUrl) {
                $this->message = 'Could not find a valid RSS feed at this URL. Please try the direct RSS feed URL.';
                $this->messageType = 'error';
                return;
            }
            
            // Check if feed already exists for this user
            $existingFeed = Auth::user()->feeds()->where('feed_url', $feedUrl)->first();
            if ($existingFeed) {
                $this->message = 'This feed is already in your collection.';
                $this->messageType = 'warning';
                return;
            }
            
            // Create new feed
            $feed = Auth::user()->feeds()->create([
                'title' => 'Loading...',
                'url' => $this->url,
                'feed_url' => $feedUrl,
                'description' => '',
            ]);
            
            // Fetch initial feed data
            try {
                $items = $this->rssFeedService->fetchFeed($feed);
                $this->rssFeedService->storeFeedItems($feed, $items);
                
                $this->message = 'Feed added successfully! Fetched ' . count($items) . ' items.';
                $this->messageType = 'success';
            } catch (\Exception $e) {
                $this->message = 'Feed added but failed to fetch initial content. Will retry later.';
                $this->messageType = 'warning';
            }
            
            $this->url = '';
            $this->showAddForm = false;
            
            // Refresh the feed list
            $this->dispatch('feed-added');
            
        } catch (\Exception $e) {
            $this->message = 'Error adding feed: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }
    
    #[On('refresh-feed')]
    public function refreshFeed($feedId)
    {
        try {
            $feed = Auth::user()->feeds()->findOrFail($feedId);
            $items = $this->rssFeedService->fetchFeed($feed);
            $newItemsCount = $this->rssFeedService->storeFeedItems($feed, $items);
            
            $this->message = "Feed refreshed! Found {$newItemsCount} new items.";
            $this->messageType = 'success';
            
            $this->dispatch('feed-refreshed', feedId: $feedId);
        } catch (\Exception $e) {
            $this->message = 'Error refreshing feed: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }
    
    public function deleteFeed($feedId)
    {
        try {
            $feed = Auth::user()->feeds()->findOrFail($feedId);
            $feed->delete();
            
            $this->message = 'Feed deleted successfully.';
            $this->messageType = 'success';
            
            $this->dispatch('feed-deleted');
        } catch (\Exception $e) {
            $this->message = 'Error deleting feed: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }
    
    public function clearMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.feed-manager');
    }
}
