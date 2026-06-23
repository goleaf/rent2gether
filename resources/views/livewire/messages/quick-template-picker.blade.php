<section class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="lg" level="2">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="chat-bubble-left-right" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('messages.actions.quick_templates') }}</span>
            </span>
        </flux:heading>
    </div>

    <div class="flex gap-2 overflow-x-auto pb-1">
        @forelse($templates as $template)
            <flux:button type="button" size="sm" variant="ghost" class="shrink-0" icon="chat-bubble-left-right">
                {{ $template['label'] }}
            </flux:button>
        @empty
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('messages.empty_states.templates') }}</flux:text>
        @endforelse
    </div>
</section>
