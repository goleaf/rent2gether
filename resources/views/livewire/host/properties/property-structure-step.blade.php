<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('property.steps.structure.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('property.steps.structure.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="chat-bubble-left-right">
                <flux:callout.text>{{ __('property.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['totalArea', 'livingArea', 'roomsCount', 'bedroomsCount', 'sharedRoomsCount', 'passThroughRoomsCount', 'bathroomsCount', 'showersCount', 'kitchensCount', 'balconiesCount', 'maxResidents'] as $field)
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.'.\Illuminate\Support\Str::snake($field)) }}</span>
    </span>
</flux:label>
                    <flux:input type="number" inputmode="decimal" wire:model.blur="{{ $field }}" icon="numbered-list" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="space-y-3">
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="canBookWholeProperty" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.can_book_whole_property') }}</span>
                    </span>
                </flux:label>
                <flux:error name="canBookWholeProperty" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="canBookPrivateRoom" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.can_book_private_room') }}</span>
                    </span>
                </flux:label>
                <flux:error name="canBookPrivateRoom" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="canBookSleepingPlace" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.can_book_sleeping_place') }}</span>
                    </span>
                </flux:label>
                <flux:error name="canBookSleepingPlace" />
            </flux:field>
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled" icon="chat-bubble-left-right">
        <span wire:loading.remove wire:target="save">{{ __('property.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('property.messages.saving') }}</span>
    </flux:button>
</form>
