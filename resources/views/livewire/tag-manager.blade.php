<div class="space-y-4">

    {{-- Header with Add Tag Button --}}
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Tags</h2>
        <flux:button square wire:click="toggleAddForm" class="cursor-pointer">
            @if($showAddForm)
                <flux:icon name="x-mark" />
            @else
                <flux:icon name="plus" />
            @endif
        </flux:button>
    </div>

    {{-- Add/Edit Tag Form --}}
    @if($showAddForm)
        <div class="bg-white dark:bg-neutral-800 p-4 rounded-lg shadow">
            <h3 class="text-lg font-medium mb-4">{{ $editingTagId ? 'Edit Tag' : 'Add New Tag' }}</h3>
            <form wire:submit.prevent="saveTag" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tag Name</label>
                    <input type="text" id="name" wire:model="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-neutral-700 dark:border-neutral-600 dark:text-white">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tag Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="color" wire:model="color" class="h-8 w-8 rounded-md border-gray-300 shadow-sm">
                        <span class="text-sm">{{ $color }}</span>
                    </div>
                    @error('color') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex justify-end gap-2">
                    <flux:button type="button" wire:click="toggleAddForm" variant="primary">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingTagId ? 'Update Tag' : 'Add Tag' }}
                    </flux:button>
                </div>
            </form>
        </div>
    @endif

    {{-- Tags List --}}
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow overflow-hidden">
        @if($tags->isEmpty())
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                No tags found. Create your first tag to categorize your feeds.
            </div>
        @else
            <ul class="divide-y divide-gray-200 dark:divide-neutral-700">
                @foreach($tags as $tag)
                    <li class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-4 h-4 rounded-full" style="background-color: {{ $tag->color }}"></span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $tag->name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">({{ $tag->feeds->count() }} feeds)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="editTag({{ $tag->id }})" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                <flux:icon name="pencil-square" class="w-5 h-5" />
                            </button>
                            <button wire:click="deleteTag({{ $tag->id }})" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                <flux:icon name="trash" class="w-5 h-5" />
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

{{-- Toast Message --}}
@if($message)
    <div 
        x-data="{ show: true }"
        x-init="
            setTimeout(() => { show = false; $wire.clearMessage() }, 5000);
            $wire.$watch('message', value => { if(value) show = true; });
        "
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="fixed bottom-4 right-4 z-50 p-4 rounded-lg shadow-lg flex items-center {{ $messageType === 'success' ? 'bg-green-100 text-green-800' : ($messageType === 'error' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}"
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
@endif
