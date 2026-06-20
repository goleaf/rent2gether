# Waitlist Feature

## Purpose

The waitlist lets a guest queue for a sleeping place when selected dates are unavailable. When the place opens, the system offers it to the first eligible guest for a limited booking window, then moves to the next guest if the offer is declined or expires.

## Data Model

- `waitlist_items` stores the guest-owned wait: property, room, sleeping place, desired dates, nights, guests, price limits, readiness flags, notification flags, position, priority, skip counters, expiry, and status timestamps.
- Legacy fields such as `desired_check_in`, `desired_check_out`, `max_price`, `ready_to_book`, and `auto_request` remain as compatibility aliases while the canonical fields are used by new code.
- `waitlist_offers` stores each concrete offer: item, guest, place, optional booking, offer expiry, current price/deposit snapshot, hold timestamps, and response timestamps.

Important indexes:

- `waitlist_items`: user/status, place/status, place/dates, property/status, room/status, status/expiry, status/check, status/priority, desired dates, and notify-available.
- `waitlist_offers`: item/status, user/status, place/status, status/offer expiry, and booking.

## Services

- `WaitlistService` joins, updates, pauses, resumes, cancels, expires, and completes waitlist items.
- `WaitlistQueueService` calculates queue positions and checks eligibility using `PricingService` and `AvailabilityService`.
- `WaitlistOfferService` creates, accepts, declines, and expires offers. Accepting an offer uses `BookingSubmit`; it does not charge payment automatically.
- `WaitlistAvailabilityService` handles no-cron triggers such as cancelled bookings, expired bookings, host-opened dates, and guest/host page checks.
- `WaitlistNotificationService` creates translated in-app notifications through the shared `NotificationService`.
- `WaitlistAutoRequestService` is a placeholder for safe future auto-request/draft behavior without automatic payment.
- `WaitlistHostViewService` provides privacy-safe demand summaries for host views.

## Livewire Components

- `JoinWaitlistButton`
- `JoinWaitlistSheet`
- `MyWaitlistPage`
- `WaitlistItemCard`
- `EditWaitlistItemSheet`
- `WaitlistOfferPage`
- `WaitlistOfferBanner`
- `HostWaitingGuestsPanel`
- `HostWaitingGuestCard`

All components keep public state small: IDs, dates, booleans, and short form fields only.

## User Flow

1. Guest selects dates for an unavailable sleeping place.
2. Guest joins the queue with optional price/deposit limits and notification preferences.
3. The system calculates queue position.
4. When availability opens, the first eligible guest gets a time-limited offer.
5. Accepting creates a booking draft/request through booking logic and leaves payment confirmation to the guest.
6. Declining or expiring the offer moves the place to the next eligible guest.

## No-Cron Behavior

The core feature works without a scheduler:

- booking cancellation calls `WaitlistAvailabilityService::handleBookingCancelled()`;
- booking expiry calls `handleBookingExpired()`;
- host-opened dates call `handleHostOpenedDates()`;
- guest and host pages can trigger checks for current records.

The optional command is available for future scheduling:

```bash
php artisan waitlist:check
```

## Mobile UX

- Waitlist pages use cards, not tables.
- Create/edit forms are bottom-sheet friendly and use short fields.
- Offer pages show the place, dates, current price, guest maximum, and countdown.
- Host demand panels show privacy-safe guest summaries only.
- Buttons use Livewire loading states and avoid large hidden DOM.

## Translation Keys

Visible UI strings live in:

- `lang/en/waitlist.php`
- `lang/ru/waitlist.php`

Notification strings live in:

- `lang/en/notifications.php`
- `lang/ru/notifications.php`

## Tests

Primary coverage is in `tests/Feature/WaitlistFeatureTest.php`:

- join/update/pause/resume/cancel
- duplicate prevention
- queue position and skip-to-next
- eligibility by price, availability, expired dates, and skips
- offer creation and notification
- accepting offer creates booking without automatic payment
- cancelled booking triggers first eligible offer
- Livewire button/page/host panel coverage
- English/Russian route rendering
