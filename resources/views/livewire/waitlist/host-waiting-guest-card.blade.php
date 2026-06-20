<div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item->user?->name ?: __('waitlist.host.guest') }}</div>
            <div class="text-xs text-zinc-500">{{ $item->desired_check_in_date?->format('d.m') }} - {{ $item->desired_check_out_date?->format('d.m') }}</div>
        </div>
        <flux:badge size="sm">{{ __('waitlist.position') }} {{ $item->position }}</flux:badge>
    </div>
    <div class="mt-2 flex flex-wrap gap-1">
        @if($item->ready_to_book_immediately)
            <flux:badge size="sm" color="green">{{ __('waitlist.ready_to_book') }}</flux:badge>
        @endif
        @if($item->user?->phone_verified)
            <flux:badge size="sm">{{ __('waitlist.host.phone_verified') }}</flux:badge>
        @endif
        @if($item->user?->identity_verified)
            <flux:badge size="sm">{{ __('waitlist.host.profile_verified') }}</flux:badge>
        @endif
    </div>
</div>
