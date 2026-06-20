<div class="mx-auto max-w-4xl space-y-5">
    <section class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 space-y-1">
                <flux:badge color="emerald">{{ __('host.rooms.eyebrow') }}</flux:badge>
                <flux:heading size="xl" level="1">{{ $this->propertyDisplay['title'] }}</flux:heading>
                @if($this->propertyDisplay['location'])
                    <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $this->propertyDisplay['location'] }}</flux:text>
                @endif
            </div>

            <flux:button
                size="sm"
                href="{{ route('host.properties.edit', ['locale' => app()->getLocale(), 'property' => $propertyId]) }}"
                icon="pencil"
                wire:navigate
            >
                {{ __('app.actions.edit') }}
            </flux:button>
        </div>

        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('host.rooms.helper') }}</flux:text>
    </section>

    @if(session('success'))
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ session('success') }}</flux:callout.text>
        </flux:callout>
    @endif

    @include('livewire.shell.partials.host-property-card', ['property' => $propertySummary])

    <div class="sticky top-3 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-sm backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
        <flux:button
            class="w-full"
            href="{{ route('host.rooms.create', ['locale' => app()->getLocale(), 'property' => $propertyId]) }}"
            variant="primary"
            icon="plus"
            wire:navigate
        >
            {{ __('host.add_room') }}
        </flux:button>
    </div>

    <div class="space-y-4">
        @forelse($this->rooms as $room)
            <flux:card class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:heading size="sm">{{ $room['title'] }}</flux:heading>
                            <flux:badge size="sm">{{ $room['status_label'] }}</flux:badge>
                        </div>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                            {{ $room['type_label'] }} · {{ $room['gender_label'] }} · {{ __('host.room_wizard.fields.max_guests') }}: {{ $room['max_guests'] }}
                            @if($room['area'])
                                · {{ __('host.room_wizard.fields.area') }}: {{ $room['area'] }}
                            @endif
                        </flux:text>
                    </div>
                </div>

                @if($room['description'])
                    <flux:text size="sm">{{ $room['description'] }}</flux:text>
                @else
                    <div class="rounded-lg border border-dashed border-zinc-200 px-3 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                        {{ __('host.rooms.empty_description') }}
                    </div>
                @endif

                @if($room['notes'])
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $room['notes'] }}</flux:text>
                @endif

                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="flex items-center justify-between gap-3">
                        <flux:heading size="sm">{{ __('host.room_wizard.readiness.title') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">
                            {{ __('host.rooms.sleeping_places_count', ['count' => $room['sleeping_places_count'], 'target' => $room['beds_count']]) }}
                        </flux:text>
                    </div>

                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach($room['readiness'] as $item)
                            <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 text-sm dark:bg-zinc-900">
                                <span>{{ $item['label'] }}</span>
                                @if($item['done'])
                                    <flux:badge size="sm" color="green">{{ __('host.room_wizard.readiness.done') }}</flux:badge>
                                @else
                                    <flux:badge size="sm">{{ __('host.room_wizard.readiness.later') }}</flux:badge>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-2 sm:grid-cols-4">
                    <flux:button
                        size="sm"
                        href="{{ route('host.rooms.edit', ['locale' => app()->getLocale(), 'property' => $propertyId, 'room' => $room['id']]) }}"
                        icon="pencil"
                        wire:navigate
                    >
                        {{ __('app.actions.edit') }}
                    </flux:button>

                    <flux:button
                        size="sm"
                        href="{{ route('host.sleeping-places.index', ['locale' => app()->getLocale(), 'room' => $room['id']]) }}"
                        variant="ghost"
                        wire:navigate
                    >
                        {{ __('host.rooms.actions.manage_places') }}
                    </flux:button>

                    <flux:button
                        size="sm"
                        type="button"
                        variant="ghost"
                        wire:click="duplicateRoom({{ $room['id'] }})"
                        class="data-loading:opacity-70"
                    >
                        {{ __('host.rooms.actions.duplicate') }}
                    </flux:button>

                    @if($room['needs_sleeping_places'])
                        <flux:button
                            size="sm"
                            type="button"
                            variant="ghost"
                            wire:click="generateSleepingPlaces({{ $room['id'] }})"
                            class="data-loading:opacity-70"
                        >
                            {{ __('host.rooms.actions.generate_places') }}
                        </flux:button>
                    @endif

                    @if($room['can_delete_draft'])
                        <flux:button
                            size="sm"
                            type="button"
                            variant="ghost"
                            icon="trash"
                            wire:click="deleteDraftRoom({{ $room['id'] }})"
                            wire:confirm="{{ __('host.rooms.actions.delete_draft_confirm') }}"
                            class="data-loading:opacity-70"
                        >
                            {{ __('host.rooms.actions.delete_draft') }}
                        </flux:button>
                    @endif
                </div>
            </flux:card>
        @empty
            <flux:card class="space-y-3 text-center">
                <flux:heading size="sm">{{ __('host.rooms.empty_title') }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('host.empty_rooms') }}</flux:text>
                <flux:button
                    href="{{ route('host.rooms.create', ['locale' => app()->getLocale(), 'property' => $propertyId]) }}"
                    variant="primary"
                    icon="plus"
                    wire:navigate
                >
                    {{ __('host.add_room') }}
                </flux:button>
            </flux:card>
        @endforelse
    </div>

    <flux:button href="{{ route('host.properties.index', ['locale' => app()->getLocale()]) }}" variant="ghost" wire:navigate>
        {{ __('host.back_to_properties') }}
    </flux:button>
</div>
