<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('booking.review.title') }}</flux:heading>

    <flux:card>
        <flux:text class="text-zinc-500">
            {{ $booking->bed->title }} &middot;
            {{ $booking->check_in->translatedFormat('d M') }} - {{ $booking->check_out->translatedFormat('d M Y') }}
        </flux:text>
    </flux:card>

    <form wire:submit="submit" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('booking.review.ratings') }}</flux:heading>

            @php
                $ratingFields = $type === 'guest_to_place'
                    ? ['overall' => 'Overall', 'cleanliness' => 'Cleanliness', 'safety' => 'Safety', 'location' => 'Location', 'accuracy' => 'Accuracy', 'bed_comfort' => 'Bed comfort', 'amenities' => 'Amenities', 'communication' => 'Communication', 'value' => 'Value']
                    : ['overall' => 'Overall', 'rule_compliance' => 'Rule compliance', 'tidiness' => 'Tidiness', 'communication' => 'Communication', 'punctuality' => 'Punctuality'];
            @endphp

            @foreach($ratingFields as $field => $label)
                <div class="flex items-center justify-between">
                    <flux:text>{{ __($label) }}</flux:text>
                    <flux:select wire:model="ratings.{{ $field }}" class="w-20">
                        <option value="">-</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </flux:select>
                </div>
            @endforeach
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('booking.review.comments') }}</flux:heading>
            <flux:textarea wire:model="commentOverall" label="{{ __('booking.review.overall') }}" rows="4" :error="$errors->first('commentOverall')" />
            <flux:textarea wire:model="commentPros" label="{{ __('booking.review.pros') }}" rows="2" />
            <flux:textarea wire:model="commentCons" label="{{ __('booking.review.cons') }}" rows="2" />
        </flux:card>

        <flux:checkbox wire:model="wouldRecommend" label="{{ __('booking.review.recommend') }}" />

        <flux:button type="submit" variant="primary">{{ __('booking.review.submit') }}</flux:button>
    </form>
</div>
