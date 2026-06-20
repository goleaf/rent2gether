<div class="mx-auto max-w-3xl space-y-5">
    <section class="space-y-2">
        <flux:badge color="emerald">{{ __('listing_wizard.eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">{{ __('listing_wizard.title') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('listing_wizard.helper') }}</flux:text>
    </section>

    @if($property)
        <livewire:host.listings.listing-wizard-progress :property-id="$property->id" :key="'listing-progress-'.$property->id" />
    @endif

    @if($wasSaved && $property)
        <livewire:host.listings.listing-draft-save-indicator :property-id="$property->id" :key="'listing-saved-'.$property->id" />
    @endif

    <section class="space-y-4">
        <div class="space-y-1">
            <flux:heading size="lg">{{ __('listing_wizard.steps.'.$step) }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('listing_wizard.step_counter', ['current' => $currentIndex, 'total' => count($steps)]) }}
            </flux:text>
        </div>

        @if($property)
            @switch($step)
                @case('property')
                    <livewire:host.listings.steps.property-step :property-id="$property->id" :key="'wizard-property-'.$property->id" />
                    @break

                @case('rooms')
                    <livewire:host.listings.steps.rooms-step :property-id="$property->id" :key="'wizard-rooms-'.$property->id" />
                    @break

                @case('sleeping_places')
                    <livewire:host.listings.steps.sleeping-places-step :property-id="$property->id" :key="'wizard-places-'.$property->id" />
                    @break

                @case('calendar')
                    <livewire:host.listings.steps.calendar-step :property-id="$property->id" :key="'wizard-calendar-'.$property->id" />
                    @break

                @case('publish')
                    <livewire:host.listings.steps.publish-step :property-id="$property->id" :key="'wizard-publish-'.$property->id" />
                    @break
            @endswitch
        @endif
    </section>

    <div class="sticky bottom-20 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
        <div class="grid grid-cols-3 gap-2">
            <flux:button type="button" variant="ghost" wire:click="back" :disabled="$step === 'property'">
                {{ __('listing_wizard.back') }}
            </flux:button>
            <flux:button type="button" variant="ghost" wire:click="saveDraft" wire:loading.attr="disabled">
                {{ __('listing_wizard.save_draft') }}
            </flux:button>
            @if($step === 'publish')
                <flux:button type="button" variant="primary" wire:click="publish" wire:loading.attr="disabled">
                    {{ __('listing_wizard.publish') }}
                </flux:button>
            @else
                <flux:button type="button" variant="primary" wire:click="next" wire:loading.attr="disabled">
                    {{ __('listing_wizard.next') }}
                </flux:button>
            @endif
        </div>
    </div>
</div>
