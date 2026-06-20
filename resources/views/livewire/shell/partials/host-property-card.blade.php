<flux:card class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <flux:heading size="lg" class="truncate">{{ $property['title'] }}</flux:heading>
                <flux:badge color="{{ $property['status_color'] }}">{{ $property['status_label'] }}</flux:badge>
            </div>

            @if($property['location'])
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $property['location'] }}</flux:text>
            @endif
        </div>

        <div class="shrink-0 text-right">
            <div class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $property['readiness'] }}%</div>
            <flux:text size="xs" class="text-zinc-500 dark:text-zinc-400">{{ __('host.listings.readiness.label') }}</flux:text>
        </div>
    </div>

    <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800" aria-label="{{ __('host.listings.readiness.label') }}">
        <div class="h-full rounded-full bg-emerald-500" style="width: {{ $property['readiness'] }}%"></div>
    </div>

    <div class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host.listings.metrics.rooms') }}</div>
            <div class="font-medium">{{ $property['counts']['rooms'] }}</div>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host.listings.metrics.sleeping_places') }}</div>
            <div class="font-medium">{{ $property['counts']['sleeping_places'] }}</div>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host.listings.metrics.free_places') }}</div>
            <div class="font-medium">{{ $property['counts']['free_places'] }}</div>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('host.listings.metrics.occupied_places') }}</div>
            <div class="font-medium">{{ $property['counts']['occupied_places'] }}</div>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <flux:badge color="emerald">{{ __('host.listings.metrics.active_count', ['count' => $property['counts']['active_places']]) }}</flux:badge>
        <flux:badge color="amber">{{ __('host.listings.metrics.draft_count', ['count' => $property['counts']['draft_places']]) }}</flux:badge>
        <flux:badge color="zinc">{{ __('host.listings.metrics.hidden_count', ['count' => $property['counts']['hidden_places']]) }}</flux:badge>
        @if($property['counts']['pending_requests'] > 0)
            <flux:badge color="sky">{{ __('host.listings.metrics.pending_count', ['count' => $property['counts']['pending_requests']]) }}</flux:badge>
        @endif
    </div>

    <div class="grid gap-2 sm:grid-cols-2">
        @foreach($property['checks'] as $check)
            <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-800">
                <span>{{ __($check['label_key']) }}</span>
                <flux:badge size="sm" color="{{ $check['done'] ? 'emerald' : 'zinc' }}">
                    {{ $check['done'] ? __('host.listings.readiness.done') : __('host.listings.readiness.later') }}
                </flux:badge>
            </div>
        @endforeach
    </div>

    @if($property['tips'])
        <div class="space-y-2">
            <flux:text size="sm" class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('host.listings.tips_title') }}</flux:text>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($property['tips'] as $tip)
                    <div class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900 dark:bg-amber-950 dark:text-amber-100">
                        {{ __($tip['label_key'], $tip['params'] ?? []) }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid gap-2 sm:grid-cols-3">
        <flux:button href="{{ route('host.properties.show', ['locale' => app()->getLocale(), 'property' => $property['id']]) }}" variant="primary" wire:navigate>
            {{ __('host.listings.actions.open_property') }}
        </flux:button>
        <flux:button href="{{ route('host.listings.create', ['locale' => app()->getLocale(), 'propertyId' => $property['id']]) }}" variant="ghost" wire:navigate>
            {{ __('app.actions.edit') }}
        </flux:button>
        <flux:button href="{{ route('host.calendar', ['locale' => app()->getLocale()]) }}" variant="ghost" wire:navigate>
            {{ __('host.listings.actions.open_calendar') }}
        </flux:button>
    </div>
</flux:card>
