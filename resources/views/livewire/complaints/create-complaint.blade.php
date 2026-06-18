<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('Submit a Complaint') }}</flux:heading>

    @if($booking)
        <flux:card>
            <flux:text class="text-zinc-500">
                {{ __('Booking') }} #{{ $booking->id }} &middot; {{ $booking->bed->title }}
            </flux:text>
        </flux:card>
    @endif

    <form wire:submit="submit" class="space-y-4">
        <flux:select wire:model="type" label="{{ __('Type') }}" :error="$errors->first('type')">
            <option value="">{{ __('Select type...') }}</option>
            @foreach($this->complaintTypes() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model="urgency" label="{{ __('Urgency') }}">
            <option value="low">{{ __('Low') }}</option>
            <option value="normal">{{ __('Normal') }}</option>
            <option value="high">{{ __('High') }}</option>
            <option value="critical">{{ __('Critical') }}</option>
        </flux:select>

        <flux:textarea wire:model="description" label="{{ __('Description') }}" rows="5" :error="$errors->first('description')" />

        <flux:textarea wire:model="desiredResolution" label="{{ __('Desired resolution (optional)') }}" rows="3" />

        <flux:button type="submit" variant="primary">{{ __('Submit complaint') }}</flux:button>
    </form>
</div>
