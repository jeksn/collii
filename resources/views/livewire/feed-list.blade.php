<div class="space-y-4">
    @if($message)
        <div class="p-4 mb-4 rounded-lg {{ $messageType === 'success' ? 'bg-green-100 text-green-800' : ($messageType === 'error' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
            {{ $message }}
        </div>
    @endif
    
    {{-- Quick Filter Buttons --}}
    <div class="flex flex-wrap justify-between gap-2 bg-gray-50 dark:bg-neutral-900 rounded-lg">
        <flux:button.group class="w-full">
            <flux:button 
                wire:click="showAllItems" 
                class="{{ $viewMode === 'all' ? 'opacity-100' : 'opacity-80' }}"
            >
                All Items
            </flux:button>
            
            <flux:button 
                wire:click="showUnreadOnly" 
                class="{{ $viewMode === 'unread' ? 'opacity-100' : 'opacity-80' }} w-full"
            >
                Unread
            </flux:button>
            
            <flux:button 
                wire:click="showStarredOnly" 
                class="{{ $viewMode === 'starred' ? 'opacity-100' : 'opacity-80' }}"
            >
                Starred
            </flux:button>
        </flux:button.group>
    </div>
    
    {{-- Tags Filter --}}
    @if(!empty($tags))
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Filter by Tag</h3>
        <div class="flex flex-wrap gap-2">
            @if($selectedTagId)
                <button 
                    wire:click="clearTagFilter"
                    class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-full text-xs hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                >
                    <span>Clear Filter</span>
                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endif
            
            @foreach($tags as $tag)
                <button 
                    wire:click="selectTag({{ $tag['id'] }})"
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs {{ $selectedTagId == $tag['id'] ? 'ring-2 ring-blue-500' : '' }}"
                    style="background-color: {{ $tag['color'] }}; color: {{ $this->getContrastColor($tag['color']) }}"
                >
                    <span>{{ $tag['name'] }}</span>
                    <span class="ml-1 bg-white text-black bg-opacity-30 rounded-full px-1">{{ $tag['feeds_count'] }}</span>
                </button>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Feed List --}}
    <div class="space-y-3 cursor-pointer">
        @forelse($feeds as $feed)
		<div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
			<div class="flex items-center justify-between">
                    <button 
                        wire:click="selectFeed({{ $feed['id'] }})"
                        class="flex-1 text-left "
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
                                <h3 class="font-medium text-gray-900 dark:text-white truncate transition-colors">
                                    {{ $feed['title'] }}
                                </h3>
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                        <span>{{ $feed['items_count'] }} items</span>
                                        @if($feed['unread_items_count'] > 0)
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                                {{ $feed['unread_items_count'] }} unread
                                            </span>
                                        @endif
                                    </div>
                                    
                                    {{-- Feed Tags --}}
                                    @if(isset($feed['tags']) && count($feed['tags']) > 0)
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($feed['tags'] as $tag)
                                                <span 
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs"
                                                    style="background-color: {{ $tag['color'] }}; color: {{ $this->getContrastColor($tag['color']) }}"
                                                >
                                                    {{ $tag['name'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </button>
                    
                    {{-- Feed Actions --}}
                    <div class="flex items-center gap-1">
                        <button 
                            wire:click="$parent.showTagsForm({{ $feed['id'] }})"
                            class="p-1 text-gray-400 hover:text-blue-600 transition-colors"
                            title="Manage tags"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
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
