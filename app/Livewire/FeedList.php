<?php

namespace App\Livewire;

use App\Models\Feed;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class FeedList extends Component
{
    public $selectedFeedId = null;
    public $viewMode = 'all';
    public $feeds = [];
    
    public function mount()
    {
        $this->loadFeeds();
    }
    
    #[On('feed-added')]
    #[On('feed-deleted')]
    #[On('feed-refreshed')]
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

    public function render()
    {
        return view('livewire.feed-list');
    }
}
