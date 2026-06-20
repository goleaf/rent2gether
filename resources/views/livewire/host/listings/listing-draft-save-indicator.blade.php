<flux:callout color="green" icon="check-circle">
    <flux:callout.text>
        {{ __('listing_wizard.saved') }}
        @if($lastSavedAt)
            {{ __('listing_wizard.saved_at', ['time' => \Illuminate\Support\Carbon::parse($lastSavedAt)->format('H:i')]) }}
        @endif
    </flux:callout.text>
</flux:callout>
