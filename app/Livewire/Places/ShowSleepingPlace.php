<?php

namespace App\Livewire\Places;

use App\Enums\BookingStatus;
use App\Enums\MessageThreadType;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\Booking;
use App\Models\Favorite;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\Localization\LocalizedModelContentResolver;
use App\Services\MessageService;
use App\Services\PricingService;
use App\Services\Privacy\ListingAddressVisibilityService;
use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

class ShowSleepingPlace extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    #[Url(as: 'in', except: '')]
    public string $checkIn = '';

    #[Url(as: 'out', except: '')]
    public string $checkOut = '';

    #[Url(as: 'guests', except: 1)]
    public int $guestsCount = 1;

    public bool $isFavorited = false;

    public bool $contactOpen = false;

    public string $messageBody = '';

    /** @var array<string, mixed>|null */
    public ?array $quote = null;

    public ?string $availabilityWarning = null;

    /** @var list<string> */
    public array $unavailableDates = [];

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->sleepingPlaceId = $sleepingPlace->id;

        abort_unless(
            $sleepingPlace->status === SleepingPlaceStatus::Active
            && $sleepingPlace->room?->status === RoomStatus::Active
            && $sleepingPlace->property?->status === PropertyStatus::Active,
            404,
        );

        $user = auth()->user();
        $this->isFavorited = $user instanceof User && $user->hasFavoritedSleepingPlace($sleepingPlace);
        $this->refreshQuote();
    }

    public function updatedCheckIn(): void
    {
        $this->refreshQuote();
    }

    public function updatedCheckOut(): void
    {
        $this->refreshQuote();
    }

    public function updatedGuestsCount(): void
    {
        $this->refreshQuote();
    }

    public function refreshQuote(): void
    {
        $this->resetValidation();
        $this->quote = null;
        $this->availabilityWarning = null;
        $this->unavailableDates = [];

        if ($this->checkIn === '' || $this->checkOut === '') {
            return;
        }

        try {
            $checkIn = CarbonImmutable::parse($this->checkIn)->startOfDay();
            $checkOut = CarbonImmutable::parse($this->checkOut)->startOfDay();
        } catch (\Throwable) {
            $this->availabilityWarning = __('listing.detail.booking.use_valid_dates');

            return;
        }

        if ($checkIn->isBefore(CarbonImmutable::today())) {
            $this->availabilityWarning = __('listing.detail.booking.past_dates');

            return;
        }

        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            $this->availabilityWarning = __('listing.detail.booking.checkout_after_checkin');

            return;
        }

        $place = $this->place();
        $nights = (int) $checkIn->diffInDays($checkOut);
        $guestsCount = max(1, $this->guestsCount);

        if ($guestsCount > $place->max_guests) {
            $this->availabilityWarning = trans_choice('listing.detail.booking.max_guests', $place->max_guests, [
                'count' => $place->max_guests,
            ]);

            return;
        }

        if ($place->min_nights && $nights < $place->min_nights) {
            $this->availabilityWarning = trans_choice('listing.detail.booking.min_nights', (int) $place->min_nights, [
                'count' => (int) $place->min_nights,
            ]);

            return;
        }

        if ($place->max_nights && $nights > $place->max_nights) {
            $this->availabilityWarning = trans_choice('listing.detail.booking.max_nights', (int) $place->max_nights, [
                'count' => (int) $place->max_nights,
            ]);

            return;
        }

        $availability = app(AvailabilityService::class);

        if (! $availability->isAvailable($place, $checkIn, $checkOut)) {
            $this->unavailableDates = $availability->unavailableDates($place, $checkIn, $checkOut);
            $this->availabilityWarning = __('listing.detail.booking.unavailable_title');

            return;
        }

        $guest = auth()->user();
        $guest = $guest instanceof User ? $guest : new User;

        $this->quote = app(PricingService::class)
            ->calculate($guest, $place, $checkIn, $checkOut, $guestsCount)
            ->toArray();
    }

    public function toggleFavorite(): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            $this->redirect(route('auth.login', ['locale' => app()->getLocale()]));

            return;
        }

        if ($this->isFavorited) {
            Favorite::query()
                ->where('user_id', $user->id)
                ->where('sleeping_place_id', $this->sleepingPlaceId)
                ->delete();

            $this->isFavorited = false;

            return;
        }

        $place = $this->place();

        Favorite::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'sleeping_place_id' => $place->id,
            ],
            [
                'bed_id' => null,
                'collection' => 'default',
                'price_at_save' => $this->quote['total_amount'] ?? $place->base_price_per_night,
                'check_in' => $this->checkIn ?: null,
                'check_out' => $this->checkOut ?: null,
                'guests_count' => max(1, $this->guestsCount),
                'notify_available' => true,
                'notify_price_drop' => true,
            ],
        );

        $this->isFavorited = true;
    }

    public function startRequest(): void
    {
        $this->openContact();

        if ($this->contactOpen && $this->messageBody === '') {
            $this->messageBody = __('listing.detail.contact.default_message', [
                'title' => $this->title($this->place()),
            ]);
        }
    }

    public function openContact(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('auth.login', ['locale' => app()->getLocale()]));

            return;
        }

        $this->contactOpen = true;
    }

    public function closeContact(): void
    {
        $this->contactOpen = false;
        $this->resetValidation('messageBody');
    }

    public function sendMessage(): void
    {
        $validated = $this->validate([
            'messageBody' => ['required', 'string', 'min:2', 'max:1000'],
        ], attributes: [
            'messageBody' => __('listing.detail.contact.message_label'),
        ]);

        $guest = auth()->user();

        if (! $guest instanceof User) {
            $this->redirect(route('auth.login', ['locale' => app()->getLocale()]));

            return;
        }

        $place = $this->place();
        $host = $place->property?->host;

        if (! $host instanceof User || $host->id === $guest->id) {
            $this->addError('messageBody', __('listing.detail.contact.unavailable'));

            return;
        }

        $thread = app(MessageService::class)->getOrCreateThread(
            guest: $guest,
            host: $host,
            type: MessageThreadType::PreBooking,
            property: $place->property,
            sleepingPlace: $place,
        );

        app(MessageService::class)->send($thread, $guest, $validated['messageBody']);

        $this->messageBody = '';
        $this->contactOpen = false;
        session()->flash('listing-contact-status', __('listing.detail.contact.sent'));
    }

    public function render(): View
    {
        $place = $this->place();
        $title = $this->title($place);

        return view('livewire.places.show-sleeping-place', [
            'place' => $place,
            'title' => $title,
            'summary' => $this->summary($place),
            'gallery' => $this->gallery(),
            'exactFeatures' => $this->exactFeatures($place),
            'roomDetails' => $this->roomDetails($place),
            'propertyDetails' => $this->propertyDetails($place),
            'nearbySummary' => $this->nearbySummary($place),
            'rulesByGroup' => $this->rulesByGroup($place),
            'faqItems' => $this->faqItems($place),
        ])->layout('layouts.app', ['title' => $title]);
    }

    #[Computed]
    public function place(): SleepingPlace
    {
        $locales = $this->translationLocales();
        $translationScope = fn ($query) => $query->whereIn('locale', $locales);
        $amenityTranslationScope = fn ($query) => $query
            ->select(['id', 'amenity_id', 'locale', 'name'])
            ->whereIn('locale', $locales);
        $ruleTranslationScope = fn ($query) => $query
            ->select(['id', 'rule_id', 'locale', 'name'])
            ->whereIn('locale', $locales);

        return SleepingPlace::query()
            ->select([
                'id',
                'room_id',
                'property_id',
                'type',
                'status',
                'place_number',
                'display_name',
                'bunk_level',
                'length_cm',
                'width_cm',
                'mattress_type',
                'mattress_firmness',
                'has_pillow',
                'has_blanket',
                'has_bedding',
                'has_towel',
                'has_curtain',
                'has_lamp',
                'has_power_socket',
                'has_usb',
                'has_shelf',
                'has_hook',
                'has_locker',
                'locker_has_lock',
                'has_luggage_space',
                'privacy_level',
                'noise_level',
                'max_guests',
                'base_price_per_night',
                'weekly_price',
                'monthly_price',
                'weekend_price',
                'cleaning_fee',
                'deposit_amount',
                'currency',
                'min_nights',
                'max_nights',
                'instant_booking_enabled',
                'requires_host_approval',
            ])
            ->withCount([
                'reviews as published_reviews_count' => fn (Builder $query) => $query->visible()->guestToPlace(),
            ])
            ->withAvg([
                'reviews as published_reviews_rating' => fn (Builder $query) => $query->visible()->guestToPlace(),
            ], 'overall_rating')
            ->with([
                'translations' => fn ($query) => $query
                    ->select(['id', 'sleeping_place_id', 'locale', 'title', 'summary', 'description', 'special_conditions'])
                    ->whereIn('locale', $locales),
                'amenities' => fn ($query) => $query->select(['amenities.id', 'amenities.slug', 'amenities.category', 'amenities.status']),
                'amenities.translations' => $amenityTranslationScope,
                'rules' => fn ($query) => $query->select(['rules.id', 'rules.slug', 'rules.category', 'rules.status']),
                'rules.translations' => $ruleTranslationScope,
                'room' => fn ($query) => $query
                    ->select([
                        'id',
                        'property_id',
                        'type',
                        'status',
                        'title',
                        'gender_policy',
                        'gender_type',
                        'area',
                        'beds_count',
                        'max_guests',
                        'occupied_places_count',
                        'available_places_count',
                        'noise_level',
                        'light_level',
                        'has_window',
                        'has_lock',
                        'has_wardrobe',
                        'has_desk',
                        'has_chair',
                        'has_mirror',
                        'has_heating',
                        'has_air_conditioning',
                        'has_balcony',
                    ])
                    ->withCount(['sleepingPlaces as active_sleeping_places_count' => fn (Builder $places) => $places->active()])
                    ->with([
                        'translations' => fn ($translation) => $translation
                            ->select(['id', 'room_id', 'locale', 'title', 'summary', 'description', 'notes'])
                            ->whereIn('locale', $locales),
                        'amenities' => fn ($amenity) => $amenity->select(['amenities.id', 'amenities.slug', 'amenities.category', 'amenities.status']),
                        'amenities.translations' => $amenityTranslationScope,
                        'rules' => fn ($rule) => $rule->select(['rules.id', 'rules.slug', 'rules.category', 'rules.status']),
                        'rules.translations' => $ruleTranslationScope,
                    ]),
                'property' => fn ($query) => $query
                    ->select([
                        'id',
                        'host_user_id',
                        'city_id',
                        'type',
                        'status',
                        'title',
                        'city',
                        'district',
                        'street',
                        'building',
                        'apartment',
                        'floor',
                        'nearest_transport',
                        'address_line_1',
                        'address_line_2',
                        'house_number',
                        'apartment_number',
                        'access_instructions',
                        'show_exact_address_before_booking',
                        'show_exact_address_after_payment',
                        'distance_to_center_meters',
                        'bathrooms_count',
                        'showers_count',
                        'kitchens_count',
                        'has_elevator',
                        'has_parking',
                        'has_security',
                        'has_cctv_common_areas',
                        'has_hot_water',
                        'safety_level',
                        'cleanliness_level',
                    ])
                    ->with([
                        'translations' => fn ($translation) => $translation
                            ->select([
                                'id',
                                'property_id',
                                'locale',
                                'title',
                                'summary',
                                'description',
                                'neighborhood_description',
                                'getting_there',
                                'what_to_know',
                                'check_in_instructions',
                                'house_rules_text',
                                'safety_notes',
                            ])
                            ->whereIn('locale', $locales),
                        'cityModel:id,name',
                        'host:id,name,avatar,languages,rating_as_host,identity_verified',
                        'host.setting:id,user_id,privacy_preferences_json',
                        'host.hostProfile:id,user_id,display_name,avatar_path,about,languages_json,response_time_minutes,response_rate,rating_average,reviews_count,verified_at,default_cancellation_policy,can_help_with_check_in,lives_nearby,lives_in_property',
                        'amenities' => fn ($amenity) => $amenity->select(['amenities.id', 'amenities.slug', 'amenities.category', 'amenities.status']),
                        'amenities.translations' => $amenityTranslationScope,
                        'rules' => fn ($rule) => $rule->select(['rules.id', 'rules.slug', 'rules.category', 'rules.status']),
                        'rules.translations' => $ruleTranslationScope,
                    ]),
            ])
            ->findOrFail($this->sleepingPlaceId);
    }

    /**
     * @return list<array{url:string,thumb_url:string,alt:string,is_primary:bool}>
     */
    private function gallery(): array
    {
        $place = $this->place();
        $targets = [
            [SleepingPlace::class, $place->id],
            [Room::class, $place->room_id],
            [Property::class, $place->property_id],
        ];

        return MediaItem::query()
            ->select(['id', 'mediable_type', 'mediable_id', 'disk', 'path', 'thumb_path', 'thumbnail_path', 'mobile_path', 'full_path', 'alt_text', 'caption_en', 'caption_ru', 'sort_order', 'is_primary', 'is_cover', 'status'])
            ->active()
            ->where(function (Builder $query) use ($targets): void {
                foreach ($targets as [$type, $id]) {
                    $query->orWhere(function (Builder $target) use ($type, $id): void {
                        $target->where('mediable_type', $type)->where('mediable_id', $id);
                    });
                }
            })
            ->orderByDesc('is_primary')
            ->orderByDesc('is_cover')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(6)
            ->get()
            ->map(fn (MediaItem $media): array => [
                'url' => $media->imageUrl('mobile'),
                'thumb_url' => $media->imageUrl('thumb'),
                'alt' => $media->localizedCaption() ?: __('listing.media.primary_alt', ['title' => $this->title($place)]),
                'is_primary' => (bool) ($media->is_primary || $media->is_cover),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(SleepingPlace $place): array
    {
        $property = $place->property;
        $hostProfile = $property?->host?->hostProfile;
        $rating = $place->published_reviews_rating ?: $hostProfile?->rating_average;
        $reviewsCount = (int) ($place->published_reviews_count ?: $hostProfile?->reviews_count ?: 0);

        return [
            'property_type' => $this->label($property?->type),
            'room_type' => $this->label($place->room?->type),
            'sleeping_place_type' => $this->label($place->type),
            'location' => $this->location($property),
            'rating' => $rating ? number_format((float) $rating, 1) : null,
            'reviews_count' => $reviewsCount,
        ];
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    private function exactFeatures(SleepingPlace $place): array
    {
        return array_values(array_filter([
            $this->row('listing.detail.exact.bed_type', $this->label($place->type)),
            $this->row('listing.detail.exact.size', __('listing.detail.values.size_cm', [
                'length' => $place->length_cm ?: '—',
                'width' => $place->width_cm ?: '—',
            ])),
            $this->row('listing.detail.exact.bunk_level', $this->valueLabel($place->bunk_level)),
            $this->row('listing.detail.exact.mattress', $this->compoundValue([$place->mattress_type, $place->mattress_firmness])),
            $this->row('listing.detail.exact.bedding_towel', $this->yesNoList([
                'listing.detail.exact.bedding' => $place->has_bedding,
                'listing.detail.exact.towel' => $place->has_towel,
            ])),
            $this->row('listing.detail.exact.power_lamp_locker', $this->yesNoList([
                'listing.detail.exact.socket' => $place->has_power_socket,
                'listing.detail.exact.lamp' => $place->has_lamp,
                'listing.detail.exact.locker' => $place->has_locker,
            ])),
            $this->row('listing.detail.exact.privacy_level', $this->valueLabel($place->privacy_level)),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    private function roomDetails(SleepingPlace $place): array
    {
        $room = $place->room;

        return [
            'people_on_dates' => $this->nearbyGuestCount($place),
            'total_places' => (int) ($room?->active_sleeping_places_count ?: $room?->beds_count ?: 0),
            'occupied_places' => (int) ($room?->occupied_places_count ?: 0),
            'gender_policy' => $this->label($room?->gender_policy ?: $room?->gender_type),
            'quiet_rules' => $this->ruleLabelsByCategories($place, ['quiet_hours', 'shared_room_behavior']),
            'amenities' => $this->amenityLabels($room?->amenities ?? collect()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function propertyDetails(SleepingPlace $place): array
    {
        $property = $place->property;
        $translation = $this->translation($property?->translations ?? collect());
        $addressVisibility = $this->addressVisibility($property);

        return [
            'description' => $translation?->description ?: $translation?->summary ?: __('listing.detail.property.no_description'),
            'address' => $addressVisibility['address'],
            'address_note' => $addressVisibility['note'],
            'check_in_instructions' => $addressVisibility['instructions'],
            'transport' => $property?->nearest_transport ?: $translation?->getting_there ?: __('listing.detail.property.transport_missing'),
            'kitchen_bathroom' => __('listing.detail.property.kitchen_bathroom_summary', [
                'kitchens' => (int) ($property?->kitchens_count ?? 0),
                'bathrooms' => (int) ($property?->bathrooms_count ?? 0),
                'showers' => (int) ($property?->showers_count ?? 0),
            ]),
            'safety' => $this->safetySummary($property, $translation?->safety_notes),
        ];
    }

    /**
     * @return array{count:int,summary:string,privacy:string}
     */
    private function nearbySummary(SleepingPlace $place): array
    {
        $count = $this->nearbyGuestCount($place);
        $room = $place->room;
        $quiet = $room?->noise_level ? $this->valueLabel($room->noise_level) : __('listing.detail.values.not_set');

        return [
            'count' => $count,
            'summary' => __('listing.detail.nearby.summary', [
                'count' => $count,
                'quiet' => $quiet,
            ]),
            'privacy' => __('listing.detail.nearby.privacy'),
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function rulesByGroup(SleepingPlace $place): array
    {
        $rules = collect()
            ->merge($place->property?->rules ?? collect())
            ->merge($place->room?->rules ?? collect())
            ->merge($place->rules ?? collect())
            ->unique('slug')
            ->groupBy(fn ($rule) => $rule->category ?: 'shared_room_behavior');

        $groups = [];

        foreach ($rules as $category => $items) {
            $groups[(string) $category] = $this->ruleLabels($items);
        }

        return $groups;
    }

    /**
     * @return list<array{question:string,answer:string}>
     */
    private function faqItems(SleepingPlace $place): array
    {
        $hostProfile = $place->property?->host?->hostProfile;

        return [
            [
                'question' => __('listing.detail.faq.bedding.question'),
                'answer' => $place->has_bedding ? __('listing.detail.faq.bedding.yes') : __('listing.detail.faq.bedding.no'),
            ],
            [
                'question' => __('listing.detail.faq.towel.question'),
                'answer' => $place->has_towel ? __('listing.detail.faq.towel.yes') : __('listing.detail.faq.towel.no'),
            ],
            [
                'question' => __('listing.detail.faq.late_checkin.question'),
                'answer' => $hostProfile?->can_help_with_check_in ? __('listing.detail.faq.late_checkin.ask_host') : __('listing.detail.faq.late_checkin.check_rules'),
            ],
            [
                'question' => __('listing.detail.faq.deposit.question'),
                'answer' => $place->deposit_amount > 0
                    ? __('listing.detail.faq.deposit.amount', ['amount' => $this->money((float) $place->deposit_amount, $place->currency)])
                    : __('listing.detail.faq.deposit.none'),
            ],
            [
                'question' => __('listing.detail.faq.cancellation.question'),
                'answer' => __('listing.detail.faq.cancellation.answer', [
                    'policy' => $this->valueLabel($hostProfile?->default_cancellation_policy ?: 'flexible'),
                ]),
            ],
            [
                'question' => __('listing.detail.faq.extension.question'),
                'answer' => __('listing.detail.faq.extension.answer'),
            ],
        ];
    }

    private function title(SleepingPlace $place): string
    {
        $translation = $this->translation($place->translations);

        return $translation?->title
            ?: $place->display_name
            ?: __('search.card.untitled', ['number' => $place->place_number ?: $place->id]);
    }

    private function translation(Collection $translations): ?object
    {
        return $this->resolver()->resolve(
            $translations,
            app()->getLocale(),
            'en',
        );
    }

    private function resolver(): LocalizedModelContentResolver
    {
        return app(LocalizedModelContentResolver::class);
    }

    /**
     * @return list<string>
     */
    private function translationLocales(): array
    {
        return array_values(array_unique(array_filter([
            app()->getLocale(),
            config('app.fallback_locale', 'en'),
            'en',
            'ru',
        ])));
    }

    private function location(?Property $property): string
    {
        $parts = array_filter([
            $property?->cityModel?->name ?: $property?->city,
            $property?->district,
        ]);

        return $parts === [] ? __('search.card.location_missing') : implode(', ', $parts);
    }

    /**
     * @return array<string, mixed>
     */
    private function addressVisibility(?Property $property): array
    {
        if (! $property) {
            return [
                'address' => __('listing.detail.property.address_missing'),
                'note' => __('listing.detail.property.address_private_note'),
                'instructions' => null,
            ];
        }

        return app(ListingAddressVisibilityService::class)->addressFor($property, auth()->user());
    }

    private function label(mixed $value): string
    {
        if ($value instanceof BackedEnum && method_exists($value, 'label')) {
            return $value->label();
        }

        return $this->valueLabel($value);
    }

    private function valueLabel(mixed $value): string
    {
        if ($value === null || $value === '') {
            return __('listing.detail.values.not_set');
        }

        $key = 'listing.detail.values.'.Str::slug((string) $value, '_');

        return Lang::has($key) ? __($key) : __('listing.detail.values.unknown');
    }

    /**
     * @param  list<mixed>  $values
     */
    private function compoundValue(array $values): string
    {
        $labels = collect($values)
            ->filter(fn ($value): bool => $value !== null && $value !== '')
            ->map(fn ($value): string => $this->valueLabel($value))
            ->values();

        return $labels->isEmpty() ? __('listing.detail.values.not_set') : $labels->join(', ');
    }

    /**
     * @param  array<string, bool>  $values
     */
    private function yesNoList(array $values): string
    {
        $active = collect($values)
            ->filter()
            ->keys()
            ->map(fn (string $key): string => __($key))
            ->values();

        return $active->isEmpty() ? __('listing.detail.values.not_set') : $active->join(', ');
    }

    /**
     * @return array{label:string,value:string}
     */
    private function row(string $labelKey, string $value): array
    {
        return ['label' => __($labelKey), 'value' => $value];
    }

    private function safetySummary(?Property $property, ?string $safetyNotes): string
    {
        $items = collect([
            $property?->has_security ? __('listing.detail.property.safety.security') : null,
            $property?->has_cctv_common_areas ? __('listing.detail.property.safety.cctv') : null,
            $property?->has_hot_water ? __('listing.detail.property.safety.hot_water') : null,
            $safetyNotes,
        ])->filter();

        return $items->isEmpty()
            ? __('listing.detail.property.safety_missing')
            : $items->join(' · ');
    }

    /**
     * @param  Collection<int, mixed>  $amenities
     * @return list<string>
     */
    private function amenityLabels(Collection $amenities): array
    {
        return $amenities
            ->filter(fn ($amenity): bool => ($amenity->status ?? 'active') === 'active')
            ->map(fn ($amenity): string => $this->translation($amenity->translations)?->name ?: $this->valueLabel($amenity->slug))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, mixed>  $rules
     * @return list<string>
     */
    private function ruleLabels(Collection $rules): array
    {
        return $rules
            ->filter(fn ($rule): bool => ($rule->status ?? 'active') === 'active')
            ->map(fn ($rule): string => $this->translation($rule->translations)?->name ?: $this->valueLabel($rule->slug))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $categories
     * @return list<string>
     */
    private function ruleLabelsByCategories(SleepingPlace $place, array $categories): array
    {
        return $this->ruleLabels(
            collect()
                ->merge($place->property?->rules ?? collect())
                ->merge($place->room?->rules ?? collect())
                ->merge($place->rules ?? collect())
                ->whereIn('category', $categories)
        );
    }

    private function nearbyGuestCount(SleepingPlace $place): int
    {
        if ($this->checkIn === '' || $this->checkOut === '') {
            return (int) ($place->room?->occupied_places_count ?: 0);
        }

        try {
            $checkIn = CarbonImmutable::parse($this->checkIn)->toDateString();
            $checkOut = CarbonImmutable::parse($this->checkOut)->toDateString();
        } catch (\Throwable) {
            return (int) ($place->room?->occupied_places_count ?: 0);
        }

        return Booking::query()
            ->where('room_id', $place->room_id)
            ->where('sleeping_place_id', '!=', $place->id)
            ->whereNotIn('status', $this->nonBlockingBookingStatuses())
            ->whereDate('check_in_date', '<', $checkOut)
            ->whereDate('check_out_date', '>', $checkIn)
            ->distinct()
            ->count('guest_user_id');
    }

    /**
     * @return list<string>
     */
    private function nonBlockingBookingStatuses(): array
    {
        return [
            BookingStatus::Draft->value,
            BookingStatus::CancelledByGuest->value,
            BookingStatus::CancelledByHost->value,
            BookingStatus::CancelledBySystem->value,
            BookingStatus::CancelledByService->value,
            BookingStatus::NoShow->value,
            BookingStatus::HostNoShow->value,
            BookingStatus::CheckedOut->value,
            BookingStatus::Completed->value,
            BookingStatus::AwaitingReview->value,
            BookingStatus::Closed->value,
        ];
    }

    private function money(float|int|string $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }
}
