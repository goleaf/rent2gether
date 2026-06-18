<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">{{ __('Write a Review') }}</flux:heading>

    <flux:card>
        <flux:text class="text-zinc-500">
            {{ $booking->bed->title }} &middot;
            {{ $booking->check_in->format('M d') }} - {{ $booking->check_out->format('M d, Y') }}
        </flux:text>
    </flux:card>

    <form wire:submit="submit" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Ratings') }}</flux:heading>

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
            <flux:heading size="sm">{{ __('Comments') }}</flux:heading>
            <flux:textarea wire:model="commentOverall" label="{{ __('Overall comment') }}" rows="4" :error="$errors->first('commentOverall')" />
            <flux:textarea wire:model="commentPros" label="{{ __('Pros') }}" rows="2" />
            <flux:textarea wire:model="commentCons" label="{{ __('Cons') }}" rows="2" />
        </flux:card>

        <flux:checkbox wire:model="wouldRecommend" label="{{ __('Would recommend') }}" />

        <flux:button type="submit" variant="primary">{{ __('Submit review') }}</flux:button>
    </form>
</div>
