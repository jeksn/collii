<?php

namespace App\Livewire;

use App\Models\Feed;
use App\Models\FeedItem;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class FeedItems extends Component
{
    use WithPagination;
    
    public $selectedFeedId = null;
    public $viewMode = 'all'; // 'all', 'unread', 'starred'
    public $search = '';
    public $selectedItem = null;
    
    #[On('feed-selected')]
    public function setSelectedFeed($feedId)
    {
        $this->selectedFeedId = $feedId;
        $this->resetPage();
    }
    
    #[On('show-all-items')]
    public function showAllItems()
    {
        $this->selectedFeedId = null;
        $this->viewMode = 'all';
        $this->resetPage();
    }
    
    #[On('show-unread-only')]
    public function showUnreadOnly()
    {
        $this->selectedFeedId = null;
        $this->viewMode = 'unread';
        $this->resetPage();
    }
    
    #[On('show-starred-only')]
    public function showStarredOnly()
    {
        $this->selectedFeedId = null;
        $this->viewMode = 'starred';
        $this->resetPage();
    }
    
    public function markAsRead($itemId)
    {
        $item = $this->findUserFeedItem($itemId);
        if ($item) {
            $item->markAsRead();
        }
    }
    
    public function markAsUnread($itemId)
    {
        $item = $this->findUserFeedItem($itemId);
        if ($item) {
            $item->markAsUnread();
        }
    }
    
    public function toggleStar($itemId)
    {
        $item = $this->findUserFeedItem($itemId);
        if ($item) {
            $item->toggleStar();
        }
    }
    
    public function markAllAsRead()
    {
        $query = $this->getItemsQuery();
        $query->update(['is_read' => true]);
    }
    
    public function selectItem($itemId)
    {
        $this->selectedItem = $itemId;
        
        // Mark as read when selected
        $this->markAsRead($itemId);
    }
    
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    protected function findUserFeedItem($itemId)
    {
        return FeedItem::whereHas('feed', function ($query) {
            $query->where('user_id', Auth::id());
        })->find($itemId);
    }
    
    protected function getItemsQuery()
    {
        $query = FeedItem::with(['feed'])
            ->whereHas('feed', function ($feedQuery) {
                $feedQuery->where('user_id', Auth::id());
            });
        
        // Filter by selected feed
        if ($this->selectedFeedId && is_numeric($this->selectedFeedId)) {
            $query->where('feed_id', $this->selectedFeedId);
        }
        
        // Filter by view mode
        switch ($this->viewMode) {
            case 'unread':
                $query->where('is_read', false);
                break;
            case 'starred':
                $query->where('is_starred', true);
                break;
        }
        
        // Search filter
        if ($this->search) {
            $query->where(function ($searchQuery) {
                $searchQuery->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('content', 'like', '%' . $this->search . '%');
            });
        }
        
        return $query->orderBy('published_at', 'desc');
    }

    public function render()
    {
        $items = $this->getItemsQuery()->paginate(20);
        
        $selectedItemData = null;
        if ($this->selectedItem) {
            $selectedItemData = $this->findUserFeedItem($this->selectedItem);
        }
        
        return view('livewire.feed-items', [
            'items' => $items,
            'selectedItemData' => $selectedItemData,
        ]);
    }
}
