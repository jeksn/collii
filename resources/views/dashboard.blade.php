<x-layouts.app :title="__('RSS Feed Reader')">
    <div class="flex h-full w-full flex-1 gap-6">
        {{-- Sidebar --}}
        <div class="w-80 flex-shrink-0 space-y-6">
            {{-- Tag Manager --}}
            <div class="bg-white dark:bg-neutral-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <livewire:tag-manager />
            </div>
            
            {{-- Feed List --}}
            <div class="bg-white flex flex-col dark:bg-neutral-900 gap-6 rounded-lg border border-gray-200 dark:border-gray-700 p-6 overflow-y-auto">
                <livewire:feed-manager />
                {{-- <livewire:feed-list /> --}}
            </div>
        </div>
        
        {{-- Main Content Area --}}
        <div class="flex-1 bg-white dark:bg-neutral-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6 overflow-y-auto">
            <livewire:feed-items />
        </div>
    </div>
</x-layouts.app>
