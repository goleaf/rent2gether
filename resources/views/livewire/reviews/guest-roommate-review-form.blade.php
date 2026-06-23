<form class="space-y-4">
    <header class="space-y-1">
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="star" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('reviews.roommates.title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('reviews.messages.roommate_privacy_notice') }}</flux:text>
    </header>

    <div class="space-y-3">
        @foreach(['quiet_roommates', 'clean_roommates', 'friendly_roommates', 'roommates_disturbed_sleep', 'conflict_happened'] as $field)
            <flux:checkbox :label="__('reviews.roommate_fields.'.$field)" wire:model.change="roommateFlags.{{ $field }}" />
        @endforeach
    </div>

    <flux:button type="button" variant="primary" class="w-full sm:w-auto" icon="eye">
        {{ __('reviews.actions.submit_review') }}
    </flux:button>
</form>
