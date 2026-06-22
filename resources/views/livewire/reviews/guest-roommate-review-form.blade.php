<form class="space-y-4">
    <header class="space-y-1">
        <flux:heading size="xl" level="1">{{ __('reviews.roommates.title') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('reviews.messages.roommate_privacy_notice') }}</flux:text>
    </header>

    <div class="space-y-3">
        @foreach(['quiet_roommates', 'clean_roommates', 'friendly_roommates', 'roommates_disturbed_sleep', 'conflict_happened'] as $field)
            <flux:checkbox :label="__('reviews.roommate_fields.'.$field)" wire:model.change="roommateFlags.{{ $field }}" />
        @endforeach
    </div>

    <flux:button type="button" variant="primary" class="w-full sm:w-auto">
        {{ __('reviews.actions.submit_review') }}
    </flux:button>
</form>
