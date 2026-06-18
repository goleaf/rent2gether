<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $property->title }}</flux:heading>
            <flux:text class="text-zinc-500">{{ $property->city }}, {{ $property->country }}</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button size="sm" href="{{ route('host.properties.edit', ['locale' => app()->getLocale(), 'property' => $property]) }}" icon="pencil">{{ __('Edit') }}</flux:button>
            <flux:button size="sm" href="{{ route('host.rooms.create', ['locale' => app()->getLocale(), 'property' => $property]) }}" variant="primary" icon="plus">{{ __('Add Room') }}</flux:button>
        </div>
    </div>

    @if(session('success'))
        <flux:badge color="green">{{ session('success') }}</flux:badge>
    @endif

    @forelse($property->rooms as $room)
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="sm">{{ $room->title }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ $room->gender_type->label() }} &middot; {{ __('Capacity') }}: {{ $room->capacity }}
                        @if($room->area_sqm) &middot; {{ $room->area_sqm }}m²@endif
                    </flux:text>
                </div>
                <div class="flex gap-2">
                    <flux:button size="sm" href="{{ route('host.rooms.edit', ['locale' => app()->getLocale(), 'property' => $property, 'room' => $room]) }}" icon="pencil" variant="ghost" />
                    <flux:button size="sm" href="{{ route('host.beds.create', ['locale' => app()->getLocale(), 'room' => $room]) }}" variant="primary" icon="plus">{{ __('Add Bed') }}</flux:button>
                    <flux:button size="sm" wire:click="deleteRoom({{ $room->id }})" variant="ghost" icon="trash" wire:confirm="{{ __('Delete this room and all its beds?') }}" />
                </div>
            </div>

            @if($room->beds->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($room->beds as $bed)
                        <div class="border rounded-lg p-3 dark:border-zinc-700 space-y-1">
                            <div class="flex items-center justify-between">
                                <flux:text class="font-medium">{{ $bed->title }}</flux:text>
                                <div class="flex gap-1">
                                    <flux:button size="sm" href="{{ route('host.beds.edit', ['locale' => app()->getLocale(), 'room' => $room, 'bed' => $bed]) }}" icon="pencil" variant="ghost" />
                                    <flux:button size="sm" wire:click="deleteBed({{ $bed->id }})" variant="ghost" icon="trash" wire:confirm="{{ __('Delete this bed?') }}" />
                                </div>
                            </div>
                            <flux:text size="sm" class="text-zinc-500">
                                {{ $bed->type->label() }} &middot; &euro;{{ number_format($bed->price_per_night, 2) }}/{{ __('night') }}
                            </flux:text>
                            <div class="flex flex-wrap gap-1">
                                @if($bed->instant_book)<flux:badge size="sm" color="green">{{ __('Instant') }}</flux:badge>@endif
                                <flux:badge size="sm">{{ $bed->status->label() }}</flux:badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <flux:text size="sm" class="text-zinc-400">{{ __('No beds in this room yet.') }}</flux:text>
            @endif
        </flux:card>
    @empty
        <flux:card>
            <flux:text class="text-center text-zinc-500 py-8">{{ __('No rooms yet. Add a room to get started.') }}</flux:text>
        </flux:card>
    @endforelse

    <flux:button href="{{ route('host.properties.index', ['locale' => app()->getLocale()]) }}" variant="ghost">{{ __('Back to properties') }}</flux:button>
</div>
