<div class="mx-auto flex min-h-screen w-full max-w-xl flex-col gap-5 px-4 pb-24 pt-6">
    <div class="space-y-2">
        <flux:heading size="lg">{{ __('rooms.edit.title') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('rooms.edit.helper') }}</flux:text>
    </div>

    @if($this->room)
        <livewire:host.rooms.room-card :room-id="$roomId" :key="'room-card-'.$roomId" />
    @else
        <div class="rounded-lg border border-zinc-200 bg-white p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
            {{ __('rooms.empty.not_found') }}
        </div>
    @endif
</div>
