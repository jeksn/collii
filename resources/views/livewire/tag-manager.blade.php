<div class="space-y-4">
    @if($message)
        <div class="p-4 mb-4 rounded-lg {{ $messageType === 'success' ? 'bg-green-100 text-green-800' : ($messageType === 'error' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
            {{ $message }}
        </div>
    @endif

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
