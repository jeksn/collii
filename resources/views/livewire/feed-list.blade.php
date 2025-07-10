<div class="space-y-2">
    {{-- Quick Filter Buttons --}}
    <div class="flex flex-wrap gap-2 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <button 
            wire:click="showAllItems" 
            class="px-3 py-1 text-sm rounded {{ $viewMode === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }} transition-colors"
        >
            All Items
        </button>
        
        <button 
            wire:click="showUnreadOnly" 
            class="px-3 py-1 text-sm rounded {{ $viewMode === 'unread' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }} transition-colors"
        >
            Unread
        </button>
        
        <button 
            wire:click="showStarredOnly" 
            class="px-3 py-1 text-sm rounded {{ $viewMode === 'starred' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }} transition-colors"
        >
            Starred
        </button>
    </div>

    {{-- Feed List --}}
    <div class="space-y-1">
        @forelse($feeds as $feed)
            <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <div class="flex items-center justify-between">
                    <button 
                        wire:click="selectFeed({{ $feed['id'] }})"
                        class="flex-1 text-left group"
                    >
                        <div class="flex items-center gap-3">
                            @if($feed['image_url'])
                                <img src="{{ $feed['image_url'] }}" alt="" class="w-8 h-8 rounded object-cover">
                            @else
                                <div class="w-8 h-8 bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900 dark:text-white truncate group-hover:text-blue-600 transition-colors">
                                    {{ $feed['title'] }}
                                </h3>
                                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span>{{ $feed['items_count'] }} items</span>
                                    @if($feed['unread_items_count'] > 0)
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                            {{ $feed['unread_items_count'] }} unread
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </button>
                    
                    {{-- Feed Actions --}}
                    <div class="flex items-center gap-1">
                        <button 
                            wire:click="$parent.refreshFeed({{ $feed['id'] }})"
                            class="p-1 text-gray-400 hover:text-blue-600 transition-colors"
                            title="Refresh feed"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                        
                        <button 
                            wire:click="$parent.deleteFeed({{ $feed['id'] }})"
                            wire:confirm="Are you sure you want to delete this feed and all its items?"
                            class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                            title="Delete feed"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-lg font-medium mb-2">No feeds yet</p>
                <p class="text-sm">Add your first RSS feed or YouTube channel to get started!</p>
            </div>
        @endforelse
    </div>
</div>
