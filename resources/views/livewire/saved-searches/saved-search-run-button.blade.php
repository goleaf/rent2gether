<flux:button type="button" variant="primary" class="w-full data-loading:opacity-70" wire:click="runNow" icon="magnifying-glass">
    <span wire:loading.remove wire:target="runNow">{{ __('saved_searches.run_now') }}</span>
    <span wire:loading wire:target="runNow">{{ __('saved_searches.checking') }}</span>
</flux:button>
