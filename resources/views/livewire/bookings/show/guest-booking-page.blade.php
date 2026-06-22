<x-ui.page class="space-y-4">
    <livewire:bookings.show.booking-main-info-card :booking-id="$summary['id']" :key="'main-'.$summary['id']" />
    <livewire:bookings.show.booking-price-card :booking-id="$summary['id']" :key="'price-'.$summary['id']" />
    <livewire:bookings.show.booking-requirements-panel :booking-id="$summary['id']" :key="'requirements-'.$summary['id']" />
    <livewire:bookings.show.booking-actions-panel :booking-id="$summary['id']" :key="'actions-'.$summary['id']" />
    <livewire:bookings.show.booking-lifecycle-timeline :booking-id="$summary['id']" :key="'lifecycle-'.$summary['id']" />
</x-ui.page>
