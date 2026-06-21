<flux:card class="space-y-3">
    <flux:heading size="sm">{{ __('booking_requests.host_view.guest_profile') }}</flux:heading>
    <div class="grid gap-2 sm:grid-cols-2">
        <flux:text>{{ $profile['public_name'] ?? __('booking_requests.empty.guest_name') }}</flux:text>
        <flux:text>{{ $profile['city'] ?? __('booking_requests.empty.city_missing') }}</flux:text>
        <flux:text>{{ __('booking_requests.host_view.identity_verified') }}: {{ ($profile['identity_verified'] ?? false) ? __('booking_requests.messages.yes') : __('booking_requests.messages.no') }}</flux:text>
        <flux:text>{{ __('booking_requests.host_view.completed_stays') }}: {{ $rating['completed_stays_count'] ?? 0 }}</flux:text>
        <flux:text>{{ __('booking_requests.host_view.reviews_count') }}: {{ $rating['reviews_count'] ?? 0 }}</flux:text>
        <flux:text>{{ __('booking_requests.host_view.rating') }}: {{ $rating['rating'] ?? __('booking_requests.empty.rating_missing') }}</flux:text>
    </div>
</flux:card>
