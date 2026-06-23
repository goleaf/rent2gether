<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('room.steps.rules.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('room.steps.rules.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="chat-bubble-left-right">
                <flux:callout.text>{{ __('room.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-4">
            @foreach($this->contentLocales() as $locale)
                <div class="grid gap-4 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700 sm:grid-cols-2">
                    <flux:heading size="sm" class="sm:col-span-2">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $locale['name'] }}</span>
                        </span>
                    </flux:heading>
                    @foreach(['room_rules_text', 'quiet_hours_text', 'food_rules_text', 'conflict_instructions', 'special_notes'] as $field)
                        <flux:field>
                            <flux:label>{{ __('room.rule_translation_fields.'.$field, ['language' => $locale['name']]) }}</flux:label>
                            <flux:textarea rows="4" wire:model.blur="translations.{{ $locale['code'] }}.{{ $field }}" />
                            <flux:error name="translations.{{ $locale['code'] }}.{{ $field }}" />
                        </flux:field>
                    @endforeach
                </div>
            @endforeach
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled" icon="chat-bubble-left-right">
        <span wire:loading.remove wire:target="save">{{ __('room.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('room.messages.saving') }}</span>
    </flux:button>
</form>
