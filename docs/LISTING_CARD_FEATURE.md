# Listing Card Feature

## Purpose

Listing cards are the fast mobile decision surface for sleeping places. They appear in search, favorites, saved-search results, comparison, waitlist cards, recommendations, and similar-place sections.

The card represents a `SleepingPlace`, not a whole property. It shows the minimum information a guest needs to open, compare, save, waitlist, or book a place without loading the full listing page.

## Data Flow

`ListingCardQueryService` creates compact sleeping-place queries with selected columns, active room/property joins, scoped review aggregates, current/fallback translations, primary media, compact amenities, compact rules, room/property context, and host trust fields.

`ListingCardService` converts loaded `SleepingPlace` models into `ListingCardData` DTOs. The DTO is then passed to Blade as an array. Livewire components store only IDs, filters, and small DTO arrays, never full model graphs.

## DTOs

- `ListingCardContext`: user, locale, currency, dates, nights, guest count, source, and compact filters.
- `ListingCardData`: sleeping-place card payload used by Blade.
- `ListingCardPriceData`: nightly price, date total, discount, deposit, cancellation, and currency.
- `ListingCardOccupancyData`: room capacity, occupied/free places, and privacy-safe occupant summary.
- `ListingCardBadgeData`: translated label, tone, and optional icon for compact badges.

## Services

- `ListingCardQueryService`: search, favorites, saved-search, comparison, recommendation, and ID-based card queries.
- `ListingCardService`: builds one or many card DTOs and batch-loads favorite, comparison, and waitlist state.
- `ListingCardPriceService`: uses `PricingService` for dated totals and falls back to nightly price without dates.
- `ListingCardBadgeService`: emits a capped badge list for price, availability, host trust, room, and booking traits.
- `ListingCardOccupancyService`: creates count-only, privacy-safe room occupancy text.
- `ListingCardAmenityRuleService`: selects only key amenities and rules.
- `ListingCardPrivacyService`: hides exact address and private guest data.

## Blade Components

- `resources/views/components/listings/card.blade.php`
- `resources/views/components/listings/card-skeleton.blade.php`
- `resources/views/components/listings/card-badges.blade.php`
- `resources/views/components/listings/card-price.blade.php`
- `resources/views/components/listings/card-amenities.blade.php`
- `resources/views/components/listings/card-rules.blade.php`

Use `card-variant`, not `variant`, when rendering the card. Flux components use `variant` internally, so the listing-card API uses a prefixed attribute to avoid leaking values such as `search` into nested Flux icons and buttons.

Example:

```blade
<x-listings.card :card="$card" card-variant="search" />
```

Use `embedded` when the card is rendered inside an existing panel and should not draw its own border or shadow:

```blade
<x-listings.card :card="$card" card-variant="favorite" embedded :show-actions="false" />
```

## Variants

- `search`: full mobile card with image, badges, price, key amenities/rules, favorite, compare, open/book, and waitlist action when unavailable.
- `compact`: smaller card for saved-search results, recommendations, and similar places.
- `favorite`: shared place summary inside favorite-specific cards with notes, priority, price-change, and collection actions around it.
- `comparison`: shared place header inside comparison columns.
- `waitlist`: shared place summary inside waitlist item cards.
- `host-preview`: reserved for host-facing preview cards.

## Integrations

- Search results now render `x-listings.card` from `SleepingPlaceSearch`.
- Favorites receive `listing_card` payloads from `FavoriteCardPresenter`.
- Saved-search results receive `listing_card` payloads from `SavedSearchResultsList`.
- Comparison columns receive `listing_card` payloads from `ComparePlaces`.
- Waitlist item cards receive `listing_card` payloads from `WaitlistItemCard`.

## Mobile UX

- Vertical layout first.
- One primary mobile image only.
- Title and summary are line-clamped.
- Price appears early.
- Tap targets are large.
- Actions are visible and translated.
- Skeleton component is available for loading states.
- No map, full gallery, full review list, or large hidden detail section is rendered in a card.

## Privacy Rules

Listing cards must not expose:

- exact street or address lines;
- private guest details;
- phones, documents, internal notes, or private messages;
- full gallery paths beyond the one card image.

Occupant text is count-only and friendly, such as "In the room: 2 guests".

## Performance Rules

- Query selected columns only.
- Batch-load favorite, comparison, waitlist, translations, primary media, amenities, and rules.
- Show at most six badges, four amenities, and three rules.
- Load only the primary mobile media variant.
- Use lazy images with explicit width and height.
- Keep card DTOs compact and paginate/list-load large screens.
- Do not calculate money, availability, or privacy rules in Blade.

## Translation Keys

Visible card copy lives in:

- `lang/en/listing_card.php`
- `lang/ru/listing_card.php`

Do not add hard-coded visible text to card Blade or Livewire components.

## Tests

Primary coverage is in `tests/Feature/ListingCardFeatureTest.php`:

- card with selected dates;
- card without dates;
- translated title and fallback behavior;
- total price and deposit display;
- unavailable dates and waitlist action;
- key amenities/rules limits;
- privacy checks for hidden exact address;
- search integration;
- English and Russian labels.

