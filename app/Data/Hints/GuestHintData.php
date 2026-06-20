<?php

namespace App\Data\Hints;

use App\Models\ListingHintSnapshot;
use Illuminate\Support\Carbon;

final readonly class GuestHintData
{
    /**
     * @param  array<string, mixed>  $messageParams
     */
    public function __construct(
        public string $key,
        public string $category,
        public string $type,
        public string $importance,
        public int $priority,
        public string $messageKey,
        public array $messageParams = [],
        public ?string $source = null,
        public ?string $icon = null,
        public ?string $tone = null,
        public bool $showOnCard = false,
        public bool $showOnDetail = true,
        public bool $showBeforeBooking = false,
        public bool $showInFavorites = false,
        public bool $showInSavedSearch = false,
        public bool $dismissible = true,
        public ?string $action = null,
        public ?Carbon $calculatedAt = null,
        public ?Carbon $expiresAt = null,
    ) {}

    public static function fromSnapshot(ListingHintSnapshot $snapshot): self
    {
        return new self(
            key: $snapshot->hint_key,
            category: $snapshot->category,
            type: $snapshot->type,
            importance: $snapshot->importance,
            priority: (int) $snapshot->priority,
            messageKey: $snapshot->message_key,
            messageParams: $snapshot->message_params_json ?? [],
            source: $snapshot->source,
            showOnCard: (bool) $snapshot->show_on_card,
            showOnDetail: (bool) $snapshot->show_on_detail,
            showBeforeBooking: (bool) $snapshot->show_before_booking,
            showInFavorites: (bool) $snapshot->show_in_favorites,
            showInSavedSearch: (bool) $snapshot->show_in_saved_search,
            dismissible: ! in_array($snapshot->importance, ['critical'], true),
            calculatedAt: $snapshot->calculated_at,
            expiresAt: $snapshot->expires_at,
        );
    }

    public function text(?string $locale = null): string
    {
        return __($this->messageKey, $this->messageParams, $locale);
    }

    public function categoryLabel(?string $locale = null): string
    {
        return __('guest_hints.categories.'.$this->category, [], $locale);
    }

    public function isCriticalBeforeBooking(): bool
    {
        return $this->showBeforeBooking
            && (! $this->dismissible || $this->importance === 'critical' || in_array($this->key, [
                'deposit_required',
                'identity_verification_required',
                'criteria_mismatch',
                'pets_forbidden',
                'smoking_forbidden',
                'address_after_booking',
            ], true));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(?string $locale = null): array
    {
        return [
            'key' => $this->key,
            'category' => $this->category,
            'category_label' => $this->categoryLabel($locale),
            'type' => $this->type,
            'importance' => $this->importance,
            'priority' => $this->priority,
            'message_key' => $this->messageKey,
            'message_params' => $this->messageParams,
            'text' => $this->text($locale),
            'source' => $this->source,
            'icon' => $this->icon,
            'tone' => $this->tone ?: $this->type,
            'show_on_card' => $this->showOnCard,
            'show_on_detail' => $this->showOnDetail,
            'show_before_booking' => $this->showBeforeBooking,
            'show_in_favorites' => $this->showInFavorites,
            'show_in_saved_search' => $this->showInSavedSearch,
            'dismissible' => $this->dismissible,
            'critical_before_booking' => $this->isCriticalBeforeBooking(),
            'action' => $this->action,
        ];
    }
}
