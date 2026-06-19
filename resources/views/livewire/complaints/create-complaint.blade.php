<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('booking.complaint.title') }}</flux:heading>

    @if($booking)
        <flux:card>
            <flux:text class="text-zinc-500">
                {{ __('booking.title') }} #{{ $booking->id }} &middot; {{ $booking->bed->title }}
            </flux:text>
        </flux:card>
    @endif

    <form wire:submit="submit" class="space-y-4">
        <flux:select wire:model="type" label="{{ __('listing.form.type') }}" :error="$errors->first('type')">
            <option value="">{{ __('booking.complaint.select_type') }}</option>
            @foreach($this->complaintTypes() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model="urgency" label="{{ __('booking.complaint.urgency') }}">
            <option value="low">{{ __('booking.complaint.low') }}</option>
            <option value="normal">{{ __('booking.complaint.normal') }}</option>
            <option value="high">{{ __('booking.complaint.high') }}</option>
            <option value="critical">{{ __('booking.complaint.critical') }}</option>
        </flux:select>

        <flux:textarea wire:model="description" label="{{ __('listing.form.description') }}" rows="5" :error="$errors->first('description')" />

        <flux:textarea wire:model="desiredResolution" label="{{ __('booking.complaint.desired_resolution') }}" rows="3" />

        <flux:button type="submit" variant="primary">{{ __('booking.complaint.submit') }}</flux:button>
    </form>
</div>
