<div class="space-y-4">
    {{-- Search and Controls --}}
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-end sm:items-center">
        {{-- <div class="flex-1 max-w-md">
            <input 
                type="text" 
                wire:model.live="search" 
                placeholder="Search articles..."
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            >
        </div> --}}
        
        <div class="flex gap-2">
            <flux:button 
                wire:click="markAllAsRead"
                wire:confirm="Mark all visible items as read?"
				variant="primary"
            >
                Mark All Read
            </flux:button>
        </div>
    </div>

    {{-- Items Grid --}}
    <div class="grid grid-cols-1 {{ $selectedItem ? 'lg:grid-cols-2' : '' }} gap-6">
        {{-- Items List --}}
        <div class="space-y-3">
            @forelse($items as $item)
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow {{ $item->is_read ? 'opacity-75' : '' }}">
                    <div class="p-4">
                        {{-- Item Header --}}
                        <div class="flex items-start gap-3 mb-3">
                            @if($item->image_url)
                                <img src="{{ $item->image_url }}" alt="" class="w-16 h-16 rounded object-cover flex-shrink-0">
                            @elseif($item->isVideo())
                                <div class="w-16 h-16 bg-red-100 rounded flex items-center justify-center flex-shrink-0">
                                    <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            @endif
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-medium text-gray-900 dark:text-white leading-tight">
                                        <button 
                                            wire:click="selectItem({{ $item->id }})"
                                            class="text-left hover:text-blue-600 transition-colors"
                                        >
                                            {{ $item->title }}
                                        </button>
                                    </h3>
                                    
                                    {{-- Item Actions --}}
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        <button 
                                            wire:click="toggleStar({{ $item->id }})"
                                            class="p-1 {{ $item->is_starred ? 'text-yellow-500' : 'text-gray-400' }} hover:text-yellow-500 transition-colors"
                                            title="{{ $item->is_starred ? 'Remove star' : 'Add star' }}"
                                        >
                                            <svg class="w-4 h-4" fill="{{ $item->is_starred ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                            </svg>
                                        </button>
                                        
                                        @if($item->is_read)
                                            <button 
                                                wire:click="markAsUnread({{ $item->id }})"
                                                class="p-1 text-gray-400 hover:text-blue-600 transition-colors"
                                                title="Mark as unread"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                            </button>
                                        @else
                                            <button 
                                                wire:click="markAsRead({{ $item->id }})"
                                                class="p-1 text-blue-600 hover:text-blue-700 transition-colors"
                                                title="Mark as read"
                                            >
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M21.99 4c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v12c0 .55.45 1 1 1h13.99c.55 0 1-.45 1-1V4zm-10 10.5L9.5 12 8 13.5 6.5 12 5 13.5V6h10v8.5z"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Item Meta --}}
                                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    <span class="font-medium text-blue-600">{{ $item->feed->title }}</span>
                                    <span>•</span>
                                    <time datetime="{{ $item->published_at?->toISOString() }}">
                                        {{ $item->published_at?->diffForHumans() }}
                                    </time>
                                    @if($item->author)
                                        <span>•</span>
                                        <span>{{ $item->author }}</span>
                                    @endif
                                    @if($item->isVideo() && $item->formatted_duration)
                                        <span>•</span>
                                        <span class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-xs">
                                            {{ $item->formatted_duration }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        {{-- Item Description --}}
                        @if($item->description)
                            <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-3 mb-3">
                                {{ Str::limit(strip_tags($item->description), 200) }}
                            </p>
                        @endif
                        
                        {{-- Item Link --}}
                        <div class="flex items-center justify-between">
                            <a 
                                href="{{ $item->url }}" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                class="text-blue-600 hover:text-blue-700 text-sm font-medium inline-flex items-center gap-1"
                                onclick="@this.markAsRead({{ $item->id }})"
                            >
                                {{ $item->isVideo() ? 'Watch Video' : 'Read More' }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    @if($search)
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <p class="text-lg font-medium mb-2">No results found</p>
                        <p class="text-sm">Try adjusting your search terms</p>
                    @else
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                        <p class="text-lg font-medium mb-2">No articles yet</p>
                        <p class="text-sm">Add some feeds to start reading!</p>
                    @endif
                </div>
            @endforelse
            
            {{-- Pagination --}}
            @if($items->hasPages())
                <div class="mt-6">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
        
        {{-- Article Detail View --}}
        @if($selectedItem && $selectedItemData)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 sticky top-4">
                <div class="flex items-start justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white leading-tight">
                        {{ $selectedItemData->title }}
                    </h2>
                    <button 
                        wire:click="$set('selectedItem', null)"
                        class="text-gray-400 hover:text-gray-600 flex-shrink-0 ml-4"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                {{-- Article Meta --}}
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
                    <span class="font-medium text-blue-600">{{ $selectedItemData->feed->title }}</span>
                    <span>•</span>
                    <time datetime="{{ $selectedItemData->published_at?->toISOString() }}">
                        {{ $selectedItemData->published_at?->format('M j, Y g:i A') }}
                    </time>
                    @if($selectedItemData->author)
                        <span>•</span>
                        <span>{{ $selectedItemData->author }}</span>
                    @endif
                </div>
                
                {{-- Article Image --}}
                @if($selectedItemData->image_url)
                    <img src="{{ $selectedItemData->image_url }}" alt="" class="w-full h-48 object-cover rounded mb-4">
                @endif
                
                {{-- Article Content --}}
                <div class="prose prose-sm max-w-none dark:prose-invert">
                    @if($selectedItemData->content)
                        {!! $selectedItemData->content !!}
                    @elseif($selectedItemData->description)
                        {!! $selectedItemData->description !!}
                    @else
                        <p class="text-gray-500 dark:text-gray-400 italic">No content preview available.</p>
                    @endif
                </div>
                
                {{-- Article Actions --}}
                <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <button 
                            wire:click="toggleStar({{ $selectedItemData->id }})"
                            class="flex items-center gap-2 px-3 py-2 {{ $selectedItemData->is_starred ? 'text-yellow-600 bg-yellow-50' : 'text-gray-600 bg-gray-50' }} rounded-lg hover:bg-yellow-100 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="{{ $selectedItemData->is_starred ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                            {{ $selectedItemData->is_starred ? 'Starred' : 'Star' }}
                        </button>
                        
                        @if($selectedItemData->is_read)
                            <button 
                                wire:click="markAsUnread({{ $selectedItemData->id }})"
                                class="flex items-center gap-2 px-3 py-2 text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Mark Unread
                            </button>
                        @endif
                    </div>
                    
                    <a 
                        href="{{ $selectedItemData->url }}" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        {{ $selectedItemData->isVideo() ? 'Watch Video' : 'Read Original' }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
