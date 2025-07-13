<?php

namespace App\Livewire;

use App\Models\Feed;
use App\Services\RssFeedService;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeedList extends Component
{
    public $selectedFeedId = null;
    public $viewMode = 'all';
    public $feeds = [];
    public $message = '';
    public $messageType = '';
    
    protected $rssFeedService;
    
    public function mount(RssFeedService $rssFeedService)
    {
        $this->rssFeedService = $rssFeedService;
        $this->loadFeeds();
    }
    
    #[On('feed-added')]
    #[On('feed-deleted')]
    #[On('feed-refreshed')]
    #[On('items-marked-read')]
    public function loadFeeds()
    {
        $this->feeds = Auth::user()
            ->feeds()
            ->withCount(['items', 'unreadItems'])
            ->orderBy('title')
            ->get()
            ->toArray();
    }
    
    public function selectFeed($feedId)
    {
        $this->selectedFeedId = $feedId;
        $this->viewMode = 'all'; // Reset to 'all' when selecting a specific feed
        $this->dispatch('feed-selected', feedId: $feedId);
    }
    
    public function showAllItems()
    {
        $this->selectedFeedId = null;
        $this->viewMode = 'all';
        $this->dispatch('show-all-items');
    }
    
    public function showUnreadOnly()
    {
        $this->selectedFeedId = 'unread';
        $this->viewMode = 'unread';
        $this->dispatch('show-unread-only');
    }
    
    public function showStarredOnly()
    {
        $this->selectedFeedId = 'starred';
        $this->viewMode = 'starred';
        $this->dispatch('show-starred-only');
    }
    
    public function refreshAllFeeds()
    {
        try {
            $feeds = Auth::user()->feeds()->get();
            $totalNewItems = 0;
            $refreshedCount = 0;
            
            foreach ($feeds as $feed) {
                try {
                    $items = $this->rssFeedService->fetchFeed($feed);
                    $newItemsCount = $this->rssFeedService->storeFeedItems($feed, $items);
                    $totalNewItems += $newItemsCount;
                    $refreshedCount++;
                } catch (\Exception $e) {
                    // Continue with other feeds if one fails
                    Log::error('Error refreshing feed: ' . $e->getMessage());
                    continue;
                }
            }
            
            if ($refreshedCount > 0) {
                $this->message = "Refreshed {$refreshedCount} feeds! Found {$totalNewItems} new items.";
                $this->messageType = 'success';
            } else {
                $this->message = "No feeds were refreshed. Please try again.";
                $this->messageType = 'warning';
            }
            
            // Refresh the feed list
            $this->loadFeeds();
            
        } catch (\Exception $e) {
            $this->message = 'Error refreshing feeds: ' . $e->getMessage();
            $this->messageType = 'error';
            Log::error('Error in refreshAllFeeds: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.feed-list');
    }
}
