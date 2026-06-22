<section class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="lg" level="2">{{ __('messages.actions.quick_templates') }}</flux:heading>
    </div>

    <div class="flex gap-2 overflow-x-auto pb-1">
        @forelse($templates as $template)
            <flux:button type="button" size="sm" variant="ghost" class="shrink-0">
                {{ $template['label'] }}
            </flux:button>
        @empty
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('messages.empty_states.templates') }}</flux:text>
        @endforelse
    </div>
</section>
