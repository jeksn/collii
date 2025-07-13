<div>
    <div class="space-y-4">
        {{-- Add Feed Button --}}
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">RSS Feeds</h2>
            <div class="flex gap-2">
                <flux:button 
                    wire:click="$dispatch('refresh-all-feeds')" class="cursor-pointer"
                    square
                    title="Refresh all feeds"
                >
                    <flux:icon name="arrow-path" />
                </flux:button>

                <flux:button square wire:click="toggleAddForm" class="cursor-pointer">
                    @if($showAddForm)
                        <flux:icon name="x-mark" />
                    @else
                        <flux:icon name="plus" />
                    @endif
                </flux:button>
            </div>
        </div>

        {{-- Add Feed Form --}}
        @if($showAddForm)
            <div class="p-6 bg-white dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700">
                <form wire:submit="addFeed" class="space-y-4">
                    <div>
                        <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Feed URL or Website URL
                        </label>
                        <input 
                            type="url" 
                            id="url"
                            wire:model="url" 
                            placeholder="https://example.com or https://www.youtube.com/@channel"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required
                        >
                        @error('url') 
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                    
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <p><strong>Supported formats:</strong></p>
                        <ul class="list-disc list-inside mt-1 space-y-1">
                            <li>Direct RSS/Atom feed URLs</li>
                            <li>Website URLs (we'll auto-discover the feed)</li>
                            <li>YouTube channel URLs (e.g., youtube.com/@channel or youtube.com/channel/ID)</li>
                        </ul>
                    </div>
                    
                    <div class="flex gap-3">
                        <flux:button 
                            type="submit" 
                            class="cursor-pointer"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50"
                        >
                            <span wire:loading.remove>Add Feed</span>
                            <span wire:loading>Adding...</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        @endif
        
        {{-- Feed List with Tag Management --}}
        <div class="mt-4">
            <livewire:feed-list />
        </div>
    </div>
    
    {{-- Edit Tags Form --}}
    @if($showEditTagsForm)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-neutral-800 p-6 rounded-lg shadow-lg max-w-md w-full">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Manage Tags</h3>
                    <button wire:click="hideTagsForm" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <flux:icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>
                
                <form wire:submit.prevent="updateFeedTags" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Tags</label>
                        
                        @if($tags->isEmpty())
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                No tags available. Create tags first to categorize your feeds.
                            </div>
                        @else
                            <div class="space-y-2 max-h-60 overflow-y-auto p-2 border border-gray-200 dark:border-neutral-700 rounded-lg">
                                @foreach($tags as $tag)
                                    <label class="flex items-center space-x-2 p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded">
                                        <input 
                                            type="checkbox" 
                                            wire:model="selectedTags" 
                                            value="{{ $tag->id }}" 
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                        >
                                        <span class="inline-flex items-center">
                                            <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: {{ $tag->color }}"></span>
                                            <span>{{ $tag->name }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex justify-end gap-2">
                        <flux:button type="button" wire:click="hideTagsForm" variant="primary">
                            Cancel
                        </flux:button>
                        <flux:button type="submit">
                            Save Tags
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Toast Message --}}
    <div 
        x-data="{ show: false, message: @entangle('message'), messageType: @entangle('messageType') }"
        x-init="
            $watch('message', value => {
                if(value) {
                    show = true;
                    setTimeout(() => {
                        show = false;
                        $wire.clearMessage();
                    }, 5000);
                }
            });
        "
        x-show="show && message"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="fixed bottom-4 right-4 z-50 p-4 rounded-lg shadow-lg flex items-center" 
        :class="messageType === 'success' ? 'bg-green-100 text-green-800' : (messageType === 'error' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')"
    >
        <div class="mr-3">
            @if($messageType === 'success')
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            @elseif($messageType === 'error')
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            @else
                <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            @endif
        </div>
        <div>
            {{ $message }}
        </div>
        <button 
            @click="show = false"
            class="ml-auto text-gray-400 hover:text-gray-600"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>
