<div class="space-y-4">
    <livewire:bookings.quotes.booking-quote-summary :quote-id="$quoteId" :key="'quote-panel-summary-'.$quoteId" />
    <livewire:bookings.quotes.booking-quote-line-breakdown :quote-id="$quoteId" :key="'quote-panel-lines-'.$quoteId" />
    <livewire:bookings.quotes.booking-quote-validation-messages :quote-id="$quoteId" :key="'quote-panel-validation-'.$quoteId" />
    <livewire:bookings.quotes.cancellation-date-preview :quote-id="$quoteId" :key="'quote-panel-cancellation-'.$quoteId" />
    <livewire:bookings.quotes.timeline-date-preview :quote-id="$quoteId" :key="'quote-panel-timeline-'.$quoteId" />
</div>
