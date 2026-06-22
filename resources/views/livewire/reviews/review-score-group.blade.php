<div class="space-y-4">
    @foreach($scores as $scoreKey)
        <livewire:reviews.review-star-input :score-key="$scoreKey" :key="'review-score-'.$group.'-'.$scoreKey" />
    @endforeach
</div>
