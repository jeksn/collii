<?php

namespace App\Livewire;

use App\Models\Feed;
use App\Models\Tag;
use App\Services\RssFeedService;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeedList extends Component
{
    public $selectedFeedId = null;
    public $selectedTagId = null;
    public $viewMode = 'all';
    public $feeds = [];
    public $tags = [];
    public $message = '';
    public $messageType = '';
    
    protected $rssFeedService;
    
    public function mount(RssFeedService $rssFeedService)
    {
        $this->rssFeedService = $rssFeedService;
        $this->loadFeeds();
    }
    
    #[On('feed-added')]
    public function onFeedAdded()
    {
        $this->message = 'Feed added successfully!';
        $this->messageType = 'success';
        $this->loadFeeds();
    }
    
    #[On('feed-deleted')]
    public function onFeedDeleted()
    {
        $this->message = 'Feed deleted successfully!';
        $this->messageType = 'success';
        $this->loadFeeds();
    }
    
    #[On('feed-refreshed')]
    public function onFeedRefreshed($feedId = null)
    {
        if (!$feedId) {
            $this->message = 'All feeds refreshed successfully!';
        } else {
            $this->message = 'Feed refreshed successfully!';
        }
        $this->messageType = 'success';
        $this->loadFeeds();
    }
    
    #[On('items-marked-read')]
    #[On('tag-updated')]
    #[On('feed-tags-updated')]
    public function loadFeeds()
    {
        $query = Auth::user()->feeds();
        
        // Filter by tag if a tag is selected
        if ($this->selectedTagId) {
            $query->whereHas('tags', function($q) {
                $q->where('tags.id', $this->selectedTagId);
            });
        }
        
        // Get feeds with their tags and counts
        $feeds = $query->withCount(['items', 'unreadItems'])
            ->with(['tags' => function($query) {
                $query->select('tags.id', 'name', 'color');
            }])
            ->orderBy('title')
            ->get();
            
        // Convert to array while preserving the tags relationship
        $this->feeds = $feeds->map(function($feed) {
            $feedArray = $feed->toArray();
            $feedArray['tags'] = $feed->tags->toArray();
            return $feedArray;
        })->toArray();
            
        // Load tags for the sidebar
        $this->tags = Tag::where('user_id', Auth::id())
            ->withCount('feeds')
            ->orderBy('name')
            ->get()
            ->toArray();
    }
    
    public function selectFeed($feedId)
    {
        $this->selectedFeedId = $feedId;
        $this->selectedTagId = null; // Clear tag filter when selecting a specific feed
        $this->viewMode = 'all'; // Reset to 'all' when selecting a specific feed
        $this->dispatch('feed-selected', feedId: $feedId);
    }
    
    public function selectTag($tagId)
    {
        $this->selectedTagId = $tagId;
        $this->selectedFeedId = null; // Clear feed selection when filtering by tag
        $this->loadFeeds(); // Reload feeds filtered by the selected tag
        $this->dispatch('tag-selected', tagId: $tagId);
    }
    
    public function clearTagFilter()
    {
        $this->selectedTagId = null;
        $this->loadFeeds();
    }
    
    /**
     * Determine if text should be light or dark based on background color
     * 
     * @param string $hexColor
     * @return string
     */
    public function clearMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }
    
    public function getContrastColor($hexColor)
    {
        // Remove # if present
        $hexColor = ltrim($hexColor, '#');
        
        // Convert to RGB
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        
        // Calculate luminance - ITU-R BT.709 formula
        $luminance = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
        
        // Return black for bright colors and white for dark ones
        return $luminance > 0.5 ? '#000000' : '#ffffff';
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
