<div class="space-y-4">
    {{-- Messages --}}
    @if($message)
        <div class="p-4 rounded-lg {{ $messageType === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : ($messageType === 'warning' ? 'bg-yellow-50 text-yellow-700 border border-yellow-200' : 'bg-green-50 text-green-700 border border-green-200') }}">
            <div class="flex justify-between items-center">
                <span>{{ $message }}</span>
                <button wire:click="clearMessage" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Add Feed Button --}}
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">RSS Feeds</h2>
        <button 
            wire:click="toggleAddForm" 
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
            @if($showAddForm)
                Cancel
            @else
                Add Feed
            @endif
        </button>
    </div>

    {{-- Add Feed Form --}}
    @if($showAddForm)
        <div class="p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
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
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50"
                    >
                        <span wire:loading.remove>Add Feed</span>
                        <span wire:loading>Adding...</span>
                    </button>
                    
                    <button 
                        type="button" 
                        wire:click="toggleAddForm"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
