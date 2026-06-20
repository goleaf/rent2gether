<div class="mx-auto max-w-5xl space-y-5 pb-28 sm:pb-8">
    <section class="space-y-3">
        <flux:badge color="emerald">{{ __('shell.pages.host.listings.eyebrow') }}</flux:badge>
        <div class="space-y-2">
            <flux:heading size="xl" level="1">{{ __($page['title_key']) }}</flux:heading>
            <flux:text class="max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __($page['helper_key']) }}</flux:text>
        </div>

        <div class="grid gap-2 sm:grid-cols-4">
            <flux:button href="{{ route('host.listings.index', ['locale' => app()->getLocale()]) }}" variant="{{ $page['scope'] === 'all' ? 'primary' : 'ghost' }}" wire:navigate>
                {{ __('host.listings.tabs.all') }}
            </flux:button>
            <flux:button href="{{ route('host.properties.index', ['locale' => app()->getLocale()]) }}" variant="ghost" wire:navigate>
                {{ __('host.listings.tabs.properties') }}
            </flux:button>
            <flux:button href="{{ route('host.listings.scope', ['locale' => app()->getLocale(), 'scope' => 'drafts']) }}" variant="{{ $page['scope'] === 'drafts' ? 'primary' : 'ghost' }}" wire:navigate>
                {{ __('host.listings.tabs.drafts') }}
            </flux:button>
            <flux:button href="{{ route('host.listings.scope', ['locale' => app()->getLocale(), 'scope' => 'hidden']) }}" variant="{{ $page['scope'] === 'hidden' ? 'primary' : 'ghost' }}" wire:navigate>
                {{ __('host.listings.tabs.hidden') }}
            </flux:button>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        <flux:card class="text-center">
            <div class="text-2xl font-semibold">{{ $metrics['active_places'] }}</div>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host.listings.metrics.active_places') }}</flux:text>
        </flux:card>
        <flux:card class="text-center">
            <div class="text-2xl font-semibold">{{ $metrics['draft_places'] }}</div>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host.listings.metrics.draft_places') }}</flux:text>
        </flux:card>
        <flux:card class="text-center">
            <div class="text-2xl font-semibold">{{ $metrics['hidden_places'] }}</div>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host.listings.metrics.hidden_places') }}</flux:text>
        </flux:card>
        <flux:card class="text-center">
            <div class="text-2xl font-semibold">{{ $metrics['unread_messages'] }}</div>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('host.listings.metrics.unread_messages') }}</flux:text>
        </flux:card>
    </div>

    <div wire:loading.delay class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-3 text-sm text-sky-800 dark:border-sky-900 dark:bg-sky-950 dark:text-sky-200">
        {{ __('host.listings.loading') }}
    </div>

    <section class="space-y-4">
        @forelse($page['properties'] as $property)
            @include('livewire.shell.partials.host-property-card', ['property' => $property])
        @empty
            <flux:card class="space-y-4 text-center">
                <flux:heading size="lg">{{ __($page['empty_title_key']) }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __($page['empty_text_key']) }}</flux:text>
                <flux:button href="{{ route('host.listings.create', ['locale' => app()->getLocale()]) }}" variant="primary" icon="plus" wire:navigate>
                    {{ __('listing_wizard.title') }}
                </flux:button>
            </flux:card>
        @endforelse
    </section>
</div>
