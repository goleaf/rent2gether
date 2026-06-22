# AGENTS.md

## Project identity

This project is a mobile-first Laravel 13 + Livewire 4 + Flux Pro marketplace for renting individual sleeping places inside rooms and properties.

The user can be:
- Guest
- Host
- Guest + Host

Do not build any admin panel yet.
Do not create administrator, moderator, support, finance, cleaner, helper, or property manager areas.
Do not use Filament.
Do not use Livewire Volt.
Do not use Inertia.
Do not build a SPA with Vue/React.
Use Livewire class components and Blade views.

## Current file placement contract

This project is controllerless for web UI. Do not create or recreate `app/Http/Controllers/`.
Do not create controller-backed routes, root-level Blade page files under `resources/views/*.blade.php`, `resources/views/auth/`, `resources/views/beds/`, or `resources/views/search/`.
Use Livewire class components for user-facing pages and actions.
Every page or feature view rendered by `app/Livewire/...` must live under `resources/views/livewire/...`.
`resources/views/components/...` and `resources/views/layouts/...` are allowed only as Livewire support surfaces for reusable Blade components and layouts.

Before creating any new PHP or Blade file, read `docs/PROJECT_STRUCTURE.md` and extend the existing folder listed there.
If a generic Laravel tool suggests a controller, route closure, Filament resource, or old auth/search Blade wrapper, treat that suggestion as incompatible with this project.

Current replacements for deleted legacy surfaces:
- login/register: `app/Livewire/Auth/LoginPage.php`, `app/Livewire/Auth/RegisterPage.php`
- logout: `app/Livewire/Auth/LogoutButton.php`
- search: `app/Livewire/Search/SleepingPlaceSearch.php`
- legacy bed detail: `app/Livewire/Beds/ShowBed.php`
- canonical sleeping-place detail: `app/Livewire/Places/ShowSleepingPlace.php`
- profile index/edit: `app/Livewire/Profile/EditProfile.php`

## Core stack

- Laravel 13
- PHP 8.3+
- Livewire 4
- Flux Pro
- Tailwind CSS
- SQLite
- Laravel localization
- Laravel migrations, seeders, factories, policies, form validation, tests

## Flux UI usage rules

- Prefer Flux UI components over custom Blade/Tailwind markup when a documented Flux component exists.
- Before creating or modifying UI, check `docs/flux-ui-components.md` for the relevant Flux component.
- Treat every user-provided `https://fluxui.dev/...` documentation URL as authoritative project UI guidance.
- Use only documented Flux UI props, variants, events, slots, child components, and patterns.
- Do not guess Flux UI APIs. If a required behavior is not documented, ask for clarification or implement the smallest safe Laravel-compatible solution.
- Keep Flux component usage consistent across Blade views and Livewire components.
- When using forms, validation, modals, buttons, inputs, selects, tables, navigation, cards, dropdowns, tabs, or notifications, first check whether Flux UI has a documented component for that need.
- Preserve Laravel, Blade, Livewire, Tailwind CSS, localization, and mobile-first project rules.
- When documentation conflicts with existing code, prefer the official Flux UI documentation unless this project has an explicit override.
- When a new Flux UI documentation link is provided, update both `AGENTS.md` and `docs/flux-ui-components.md` before implementing related UI changes.
- For UI, Blade, Livewire, forms, layout, navigation, modal, table, or Flux work, read this file first, then read `docs/flux-ui-components.md`, then inspect existing sibling components before editing.
- If a Flux documentation URL cannot be accessed, do not infer the API from memory; ask the user to paste the relevant documentation before recording rules or implementing related UI.
- For every Flux documentation update, record the source URL, review date, documented props/attributes, slots or child components, Livewire binding notes, styling options, project rules, and mistakes to avoid in `docs/flux-ui-components.md`.
- Flux principles are binding for UI work: prefer simple syntax first, use documented composition for advanced layouts, keep component naming consistent, rely on native browser/CSS behavior where Flux provides it, and handle page spacing/layout in the application rather than by overriding Flux internals.
- Use Flux Select only for bounded choices. For lists of up to 5 items, consider documented Radio or Checkbox patterns first; for large datasets such as countries/cities, use Autocomplete or documented backend-search Select/Combobox patterns and never preload huge option lists.
- Current reviewed Flux reference pages are documented in `docs/flux-ui-components.md` and include Flux principles, patterns, theming, dark mode, customization, header/sidebar layouts, accordion, autocomplete, avatar, badge, brand, button, breadcrumbs, calendar, callout, card, carousel, chart, checkbox, color picker, command, composer, context, date picker, dropdown, editor, field, file upload, heading, icon, input, modal, navbar, OTP input, pillbox, popover, profile, progress, radio, select, separator, skeleton, slider, switch, table, tabs, text, textarea, time picker, timeline, toast, and tooltip.

## Livewire 4 documentation rules

- Treat every user-provided `https://livewire.laravel.com/docs/4.x/...` documentation URL as authoritative project guidance after reading it.
- Before implementing related Livewire behavior, check `docs/LIVEWIRE_4_REFERENCE.md`, use Laravel Boost `search-docs`, and inspect existing sibling Livewire components.
- When a new Livewire documentation URL is provided, update both `AGENTS.md` when it changes project rules and `docs/LIVEWIRE_4_REFERENCE.md` with the source URL, review date, documented API, project rules, and mistakes to avoid.
- Official Livewire examples may use view-based or single-file components. In this project, translate those examples into class components under `app/Livewire/...` and Blade views under `resources/views/livewire/...`; do not introduce Volt or inline PHP Blade components.
- Current reviewed Livewire reference pages are documented in `docs/LIVEWIRE_4_REFERENCE.md` and include Livewire Islands, Lazy Loading, Loading States, Validation, File Uploads, Pagination, URL Query Parameters, Computed Properties, and Redirecting.

## Product goal

Build a friendly mobile website where:
- Hosts can create properties, rooms, sleeping places, prices, rules, calendars, media, and availability.
- Guests can search, filter, compare, favorite, request, book, pay, check in, extend, check out, review, complain, and manage trips.
- The main rental unit is a sleeping place, not a whole apartment.
- The system automatically calculates stay days, nights, calendar days, price, discounts, deposit, cleaning fee, service fee, total due, refund/cancellation deadlines, host payout timing, reminders, and availability.
- The system supports Russian and English from day one and can add more languages later.

## Core marketplace loop

Guest chooses:
- city
- dates
- sleeping place

System calculates:
- availability
- stay days
- nights
- calendar days
- price
- discount
- deposit
- cleaning fee
- service fee
- total due
- free cancellation date
- cancellation penalty start date
- host payout date
- check-in reminder date
- check-out reminder date
- rules
- compatibility

Host controls:
- property
- rooms
- sleeping places
- calendar
- price
- rules
- requests

Everything must be mobile-first, multilingual, fast, friendly, and Livewire-native. This frame prevents drift into Filament, Volt, admin panels, desktop-first interfaces, or heavy SPA architecture.

## Advanced guest search criteria

Guest search must be flexible, but still mobile-first and fast. Search filters should be grouped into progressive bottom sheets/drawers and synchronized to URL query state when shareable.

### Location criteria

Guest search by place must support:
- country / `country_id`
- city / `city_id`
- district / `district`
- street / `street`
- landmark / `landmark`
- close to city center / `near_center`
- close to metro / `near_metro`
- close to bus stop / `near_bus_stop`
- close to train station / `near_train_station`
- close to airport / `near_airport`
- close to university / `near_university`
- close to work / `near_work`
- close to hospital / `near_hospital`
- close to sea / `near_sea`
- close to park / `near_park`
- close to shopping center / `near_shopping_center`
- close to gym / `near_gym`
- close to coworking / `near_coworking`
- close to nightlife / `near_nightlife`
- quiet area / `area_quiet`
- safe area / `area_safe`
- residential area / `area_residential`
- city center / `area_city_center`
- suburb / `area_suburb`
- industrial area / `area_industrial`
- tourist area / `area_tourist`
- student area / `area_students`
- worker area / `area_workers`
- long-stay area / `area_long_stay`

Countries and cities must use autocomplete from imported SQLite geo data. Do not load full country/city lists into selects. District, street, and landmark search must use normalized local fields or imported/local point data, not external API calls during normal search. Proximity filters must use stored property coordinates, distance fields, or offline/imported points of interest. Do not load a map on the first search screen.

## Sleeping place availability logic

Every sleeping place must have its own separate calendar. Room-level and property-level closures may cascade down, but availability is ultimately checked per `sleeping_place_id + date`.

Sleeping place date statuses must support:
- available: Свободно
- occupied: Занято
- pending_payment: Ожидает оплаты
- pending_host_confirmation: Ожидает подтверждения хозяина
- booked: Забронировано
- guest_checked_in: Гость заселился
- guest_checked_out: Гость выехал
- closed_by_host: Закрыто хозяином
- closed_by_service: Закрыто сервисом
- cleaning: На уборке
- repair: На ремонте
- broken: Недоступно по причине поломки
- complaint_blocked: Недоступно из-за жалобы
- hidden: Временно скрыто
- request_only: Доступно только по запросу

Status labels must be translated through locale files. Availability queries must use indexed `sleeping_place_id + date` lookups and must not load full calendars into Livewire public properties.

### Double booking protection

The system must prevent overlapping bookings and holds for the same sleeping place. Existing booking/hold ranges and requested ranges use `[check_in_date, check_out_date)`.

If one guest has the sleeping place from July 10 to July 15, another guest cannot book overlapping ranges such as:
- July 9 to July 11
- July 10 to July 12
- July 14 to July 16
- July 11 to July 15

Another guest may book:
- dates ending on or before July 10
- dates starting after July 15
- dates starting on July 15 only when the first guest checks out on July 15 and the host allows same-day check-in, cleaning gap rules pass, and check-in/check-out flags allow that boundary

Overlap protection must run in services/actions inside the booking/request transaction, recheck availability immediately before creating holds, and return translated friendly messages when dates are no longer available.

### Turnover time between guests

Hosts can configure the time needed between one guest checking out and the next guest checking in.

Turnover fields must support:
- minimum turnover time between checkout and check-in / `minimum_turnover_minutes`
- cleaning required between guests / `cleaning_required_between_guests`
- cleaning duration / `cleaning_duration_minutes`
- inspection required after checkout / `inspection_required_after_checkout`
- same-day check-in after previous checkout allowed / `same_day_check_in_allowed`
- morning checkout and evening check-in allowed / `morning_checkout_evening_checkin_allowed`
- earliest time for the next check-in / `earliest_next_check_in_time`
- latest time for the previous checkout / `latest_previous_check_out_time`

Same-day turnover is allowed only when host rules allow it, checkout/check-in flags allow the boundary date, the previous checkout time plus required turnover/cleaning/inspection time is before or equal to the next check-in time, and cleaning gap rules pass. Otherwise the date must be blocked or shown as unavailable/request-only with a translated explanation.

## Date selection fields

Guest date selection must support:
- check-in date
- check-in time
- check-out date
- check-out time
- nights count
- stay days count
- calendar days count
- early check-in requested
- late check-out requested
- flexible check-in time
- flexible check-out time
- host time approval required
- check-in time comment
- check-out time comment

Counts are derived by services and displayed as read-only summaries. Overnight sleeping-place rentals use `[check_in_date, check_out_date)` for billing: `nights_count` is the day difference between checkout and check-in, `stay_days_count` equals `nights_count` in the current non-hourly mode, and `calendar_days_count` is the inclusive presence display. For example, July 10 to July 13 is 3 nights / 3 payable stay days and 4 calendar presence days: July 10, 11, 12, and part of July 13. The main payable quantity is nights / stay days, not inclusive calendar days. Time comments use `wire:model.blur`; toggles and time choices use `wire:model.change`.

## Automatic date checks

Before showing a payable quote, creating a booking request, or confirming a booking, the system must reject:
- checkout before check-in
- same-day checkout unless a future daily/hourly rental mode explicitly allows it
- dates when the sleeping place is occupied or held
- dates when the property, room, or sleeping place is closed by the host
- dates when the room is unavailable or under repair
- dates that violate a required cleaning gap after checkout
- stays shorter than the minimum stay
- stays longer than the maximum stay
- check-in dates where the host has disabled check-in for that weekday
- check-out dates where the host has disabled check-out for that weekday
- bookings by guests who have not completed required verification
- bookings where the guest age does not match room rules
- bookings by male guests for female-only rooms when the host configured that format
- bookings by female guests for male-only rooms when the host configured that format
- bookings where guest count exceeds the sleeping place limit

These checks belong in services/actions with tests and translated user-facing messages, not in Blade templates.

## Automatic calendar behavior

When a guest selects a check-in date, the system must:
- highlight available check-out dates
- hide or disable unavailable check-out dates without rendering a huge hidden DOM
- show the earliest possible check-out date
- show the latest possible check-out date
- warn if the selected range contains an occupied, held, blocked, repair, cleaning, or otherwise unavailable date
- suggest the nearest available date ranges
- suggest similar sleeping places
- suggest a neighboring room where appropriate
- suggest another sleeping place from the same host where appropriate
- automatically recalculate price, discounts, deposit, cleaning fee, service fee, total due, cancellation deadlines, payout timing, and reminders when dates change

Calendar behavior must use compact service DTOs and translated reason keys. Do not load maps, full galleries, or large result lists just because dates changed.

## Automatic price calculation

When the guest changes dates, guest count, timing options, promo code, or other booking conditions, the system must automatically recalculate the price quote.

Price calculation must include:
- check-in date
- check-out date
- stay days count
- base price per stay day
- weekday price
- weekend price
- holiday price
- weekly price
- monthly price
- long-stay discount
- weekly discount
- monthly discount
- early-booking discount
- last-minute discount
- new-guest discount
- personal discount
- early check-in fee
- late check-out fee
- extra guest fee
- cleaning fee
- deposit
- service fee
- taxes or city fees when configured
- promo code
- total discount amount
- amount before discounts
- amount after discounts
- total due
- host payout amount
- refundable amount
- non-refundable amount

Pricing must be calculated by services/actions, returned as compact DTOs with line items and translation keys, and persisted to `booking_price_lines` only when a booking or booking request is created. Blade and Livewire views must never calculate money inline.

### Price logic

The pricing system must understand:
- stays shorter than 7 days use the per-stay-day price
- stays from 7 days may apply a weekly discount
- stays from 30 days may apply a monthly discount
- dates that fall on weekends use the weekend price when no stronger date price applies
- dates that fall on holidays use the holiday price when no date-specific override applies
- host date-specific prices override normal weekday, weekend, and holiday prices
- promo codes recalculate the final quote
- changing checkout date recalculates the full quote from scratch
- a second guest on a two-person sleeping place can add an extra guest fee
- early check-in adds a fee or creates a host-approval request, depending on host rules
- late check-out adds a fee or creates a host-approval request, depending on host rules

### Price display example

Guest-facing price summaries must show daily price lines before totals, then explain fees, discounts, current payment, and refundable deposit. Example structure:
- July 10: EUR 20
- July 11: EUR 20
- July 12: EUR 25
- stay days: 3
- accommodation amount: EUR 65
- discount: EUR 5
- cleaning fee: EUR 10
- deposit: EUR 50
- service fee: EUR 6
- total due now: EUR 126
- refundable after checkout: EUR 50 deposit

All labels, explanations, and refund notes must use translation keys. The example is a display contract, not hard-coded UI copy.

## Advanced booking logic

Booking classification must support a primary booking flow plus payment/deposit modes and optional booking modifiers. Do not force all booking variants into one fragile status string.

Primary booking flows:
- instant_booking: Мгновенное бронирование
- host_confirmation_booking: Бронирование с подтверждением хозяина
- stay_request: Запрос на проживание
- preliminary_inquiry: Предварительный запрос
- long_term_request: Долгосрочная заявка
- urgent_today_booking: Срочное бронирование на сегодня

Payment and deposit modes:
- awaiting_payment: Бронирование с ожиданием оплаты
- with_deposit: Бронирование с залогом
- without_deposit: Бронирование без залога
- partial_payment: Бронирование с частичной оплатой
- full_payment: Бронирование с полной оплатой

Booking modifiers and special scenarios:
- extension: Бронирование с продлением
- relocation: Бронирование с переселением на другое место
- group_booking: Бронирование для группы
- two_guest_sleeping_place: Бронирование одного места для двух гостей, если место двухместное

Use enum-like constants or PHP enums where appropriate. Every booking flow, payment/deposit mode, modifier, status label, and user-facing explanation must use translation keys.

### Booking fields

Booking records, booking DTOs, and booking detail screens must support:
- booking number / `booking_number`
- booking status / `status`
- guest / `guest_user_id`
- host / `host_user_id`
- property / `property_id`
- room / `room_id`
- sleeping place / `sleeping_place_id`
- check-in date / `check_in_date`
- check-in time / `check_in_time`
- check-out date / `check_out_date`
- check-out time / `check_out_time`
- stay days count / `stay_days_count`
- calendar days count / `calendar_days_count`
- guests count / `guests_count`
- price per stay day / `price_per_stay_day`
- price for the full period / `period_price_amount`
- discount / `discount_amount`
- deposit / `deposit_amount`
- cleaning fee / `cleaning_fee_amount`
- service fee / `service_fee_amount`
- total amount / `total_amount`
- currency / `currency`
- payment status / `payment_status`
- payment method / `payment_method`
- payment date / `paid_at`
- payment deadline / `payment_deadline_at`
- guest message / `guest_message`
- host response / `host_response`
- document verification required / `requires_document_verification`
- phone verification required / `requires_phone_verification`
- identity verification required / `requires_identity_verification`
- decline reason / `decline_reason`
- cancellation reason / `cancellation_reason`
- cancelled by / `cancelled_by`
- cancellation policy / `cancellation_policy`
- refund amount / `refund_amount`
- refund status / `refund_status`
- check-in instructions / `check_in_instructions`
- guest confirmed check-in / `guest_checked_in_at`
- host confirmed check-in / `host_confirmed_check_in_at`
- guest confirmed check-out / `guest_checked_out_at`
- host confirmed check-out / `host_confirmed_check_out_at`
- has dispute / `has_dispute`
- has complaint / `has_complaint`
- guest review submitted / `guest_review_submitted_at`
- host review submitted / `host_review_submitted_at`

Prefer timestamps over booleans for confirmations and review submission where possible. Derived mobile DTOs may expose boolean flags for UI convenience.

### Booking statuses

Booking lifecycle statuses must support:
- draft: Черновик
- created: Создано
- awaiting_host_approval: Ожидает подтверждения хозяина
- awaiting_guest_response: Ожидает ответа гостя
- awaiting_payment: Ожидает оплаты
- awaiting_identity_verification: Ожидает проверки личности
- awaiting_document_verification: Ожидает проверки документов
- confirmed: Подтверждено
- paid: Оплачено
- ready_for_check_in: Готово к заселению
- guest_checked_in: Гость заселился
- in_progress: Проживание идет
- check_out_soon: Скоро выезд
- guest_checked_out: Гость выехал
- awaiting_room_inspection: Ожидает проверки помещения
- awaiting_deposit_return: Ожидает возврата залога
- completed: Завершено
- awaiting_review: Ожидает отзыва
- closed: Закрыто
- declined_by_host: Отклонено хозяином
- cancelled_by_guest: Отменено гостем
- cancelled_by_host: Отменено хозяином
- cancelled_by_service: Отменено сервисом
- unpaid: Не оплачено
- guest_no_show: Гость не приехал
- host_unresponsive: Хозяин не вышел на связь
- dispute_opened: Возник спор
- frozen_pending_dispute_resolution: Заморожено до решения спора
- requires_support_intervention: Требует вмешательства поддержки

Every booking status label must be translated. `requires_support_intervention` is a lifecycle state only and must not create a support/staff panel. Keep `payment_status` as a separate money state even when the booking lifecycle status is `paid` or `unpaid`.

## Booking request logic

If a sleeping place is not instant-bookable, the guest sends a booking request.

Booking requests must support:
- guest
- host
- sleeping place
- check-in date
- check-out date
- stay days count
- guests count
- travel purpose
- planned arrival time
- message to host
- has luggage
- needs luggage space
- needs early check-in
- needs late check-out
- needs residence registration
- needs reporting documents
- request status
- host response
- decline reason
- request expiration time

Host request screens must show a privacy-safe guest summary, dates, stay days, total amount, travel purpose, guest message, rule compatibility, and translated warning reasons.

## Stay extension logic

Guests may extend a stay only when the current booking is active enough for extension and the sleeping place is free after the current check-out date.

Stay extension records, DTOs, and mobile screens must support:
- current booking / `booking_id`
- current check-out date / `current_check_out_date`
- new check-out date / `new_check_out_date`
- additional stay days count / `additional_stay_days_count`
- price for additional stay days / `additional_price_per_stay_day`
- extension discount / `extension_discount_amount`
- amount due for extension / `extension_amount_due`
- extension status / `status`
- host approval required / `host_approval_required`
- host response / `host_response`
- decline reason / `decline_reason`
- extension payment date / `extension_paid_at`

Extension logic must:
- check that the added range `[current_check_out_date, new_check_out_date)` is available
- check that another guest has not booked or held the same sleeping place
- recalculate the price through a service/action
- show the guest the additional amount due before confirmation
- send a host approval request when the sleeping place or host rules require it
- after approval and payment, update the original booking check-out date, stay days, totals, status history, and price lines
- update the sleeping-place calendar and availability holds for the added dates
- notify both guest and host

All extension statuses, host responses, decline reasons, price explanations, and notifications must use translation keys. Do not add finance, support, staff, or admin workflows for extension handling.

## Stay relocation logic

Guests may request relocation when the current sleeping place no longer fits, or a host may offer another sleeping place.

Relocation reasons must be enum-like translated values:
- noisy neighbors / `noisy_neighbors`
- uncomfortable bed / `uncomfortable_bed`
- conflict with another resident / `resident_conflict`
- breakage or malfunction / `broken_item`
- host offered another place / `host_offered_other_place`
- guest wants a more private room / `wants_more_private_room`
- guest wants a cheaper place / `wants_cheaper_place`
- guest wants a more expensive but more comfortable place / `wants_more_comfort`

Stay relocation records, DTOs, and mobile screens must support:
- current sleeping place / `current_sleeping_place_id`
- new sleeping place / `new_sleeping_place_id`
- relocation reason / `reason`
- relocation date / `relocation_date`
- price difference / `price_difference_amount`
- who pays the difference / `price_difference_payer`
- guest consent required / `guest_consent_required`
- host consent required / `host_consent_required`
- relocation status / `status`
- guest comment / `guest_comment`
- host comment / `host_comment`
- support comment / `support_comment`

Relocation logic must:
- link to the original booking and preserve the booking audit trail
- check that the new sleeping place is available from `relocation_date` to the booking check-out date
- keep the old sleeping place blocked before `relocation_date`
- release or convert old-place holds only after the relocation is approved/applied
- block the new sleeping place from `relocation_date` to check-out
- recalculate price difference, additional payment, refund/credit, and deposit implications through a service/action
- require guest and/or host consent when the relocation is requested or offered by the other side
- update booking status history, availability rows, price lines, and notifications after the relocation is applied

All relocation reasons, statuses, consent labels, comments, price-difference explanations, and notifications must use translation keys. `support_comment` is a reserved data field only and must not create a support/staff/admin panel or workflow yet.

## Mandatory mobile-first rules

Design every page first for 320px–430px wide screens.
The UI must work on old Android devices, including Samsung S4-class devices.
Assume slow 3G.
Avoid large DOM trees.
Avoid heavy JS.
Avoid client-side rendering frameworks.
Avoid large modal stacks.
Avoid huge select lists.
Avoid loading maps, galleries, or filters until needed.
Prefer progressive disclosure, drawers, bottom sheets, accordions, and step-by-step forms.
Use Flux components where practical.
Keep tap targets large.
Keep forms short per step.
Show skeletons and loading states for every network action.
Prefer Livewire `data-loading` styling for network-action feedback; use `wire:loading` only where a simple show/hide directive is clearer.
Use wire:navigate for internal navigation where it improves perceived speed.
Use lazy loading for below-the-fold Livewire components.

## Livewire rules

Use Livewire class components.
Do not use Livewire Volt.
Keep public properties small.
Never store huge arrays in Livewire public properties.
Store IDs, filters, and compact state only.
Use computed properties for derived data.
Use `#[Computed]` for derived display data, query-backed DTOs, and values read multiple times in one render or action.
Access computed properties in Blade through `$this`, such as `$this->results`; do not expect plain `$results` variables.
Do not use `#[Computed]` on Livewire Form Objects.
Remember normal computed properties are memoized only for one Livewire request; they are recalculated on the next update.
Use `unset($this->computedName)` after an action mutates data already read by that computed property.
Use `#[Computed(persist: true)]` or `#[Computed(cache: true)]` only with a clear cache lifetime, cache key/invalidation plan, and safe sharing scope.
Computed properties must still use selected columns, eager loading, pagination, scopes, and indexes; do not use them to hide `Model::all()` or N+1 queries.
Use form objects or dedicated component state when useful.
Use `#[Validate]` for simple static Livewire rules and always call `$this->validate()` or `$this->form->validate()` before persisting.
Use `#[Validate(..., onUpdate: false)]` when colocated rules should not run on every update.
Use a `rules()` method or Livewire Form Object rules for Laravel `Rule` objects, dynamic validation, uniqueness checks, authenticated-user constraints, cross-field date logic, and database-dependent rules.
Use Livewire Form Objects for larger forms, reusable form state, multi-step flows, and dense booking/listing/edit surfaces.
Reference form object fields in Blade and tests with their form prefix, such as `form.title`.
Use real-time validation sparingly; prefer `wire:model.live.blur` only for text fields that genuinely need early validation feedback, and avoid live validation for long textareas.
Do not use `translate: false` for user-facing validation messages.
Do not hard-code validation messages or attribute labels in `message:`, `as:`, `messages()`, `validationAttributes()`, `$this->addError()`, or custom validators; use translation keys or Laravel validation language files.
Use `assertHasErrors()` and rule-specific validation assertions in Livewire tests.
Use wire:model.blur for normal text fields.
Use wire:model.change for selects, checkboxes, radios.
Use wire:model.live.debounce.500ms or wire:model.live.debounce.750ms only for search and autocomplete fields.
Never use live typing updates for long textareas.
Never load full countries or cities into a select.
Never render hidden huge filter sections.
Use bottom sheets, drawers, and lazy components for large secondary UI.
Use pagination or cursor pagination for lists.
Use cursor pagination or load-more behavior for public search results.
Use `WithPagination` in every Livewire component that owns pagination.
Keep paginated queries in computed methods or concise query methods, not Blade templates.
Use `cursorPaginate()` for large, append-only, or feed-like datasets such as search results, messages, notifications, reviews, favorites, and activity lists.
Use `simplePaginate()` when next/previous navigation is enough and total counts are not needed.
Use `paginate()` only when numbered pages or total counts are a real user need.
Keep Livewire's URL pagination for shareable search/listing pages; use `WithoutUrlPagination` only for private widgets or embedded dashboard panels.
Call `$this->resetPage()` when search, filters, sorting, date range, tabs, or other query-shaping state changes.
Use named paginator `pageName` values when one screen contains multiple paginated lists, and pass `pageName` to pagination helper methods.
Use `links(data: ['scrollTo' => '#selector'])` for below-the-top lists when pagination should return the user to the list.
Do not switch Livewire pagination to Bootstrap; if custom pagination views are introduced, keep labels translated and controls mobile-first.
Use URL query state for search filters that should be shareable.
Use `#[Url]` only for small shareable state such as search, filters, sort keys, date/location filters, selected content tabs, and pagination-adjacent state.
Do not put sensitive or private data, access codes, exact private addresses, internal notes, payment/provider payloads, large arrays, models, DTOs, or long form bodies into URL-bound properties.
Prefer compact aliases with `#[Url(as: 'q', except: '')]`; use `except` to keep URLs clean and `keep: true` sparingly.
Use `history: true` only when the browser Back button should step through previous query values; keep the default replace-state behavior for noisy live search.
Use nullable URL properties only when an empty query value should become `null`; otherwise prefer explicit defaults.
Use `queryString()` or trait query-string hooks for dynamic or reusable URL configuration.
Validate, coerce, and whitelist URL-backed state before applying Eloquent scopes.
Reset pagination when URL-backed filters, sorts, date ranges, search, or tabs change.
Use `$this->redirect()` or `$this->redirectRoute()` from Livewire actions after successful validation, authorization, and persistence.
Prefer `redirectRoute()` with named localized routes for internal UI flows.
Use `redirectIntended()` only with a safe fallback route or URL.
Use `navigate: true` on internal redirects only when `wire:navigate` behavior is appropriate for the destination.
Do not use `redirectAction()` for new UI flows; do not create controllers just to redirect from Livewire.
Never redirect to raw user-supplied URLs or put sensitive data in redirect URLs.
Flash post-redirect messages as translation keys/context, not hard-coded visible strings.
Use events carefully and keep component boundaries simple.
Use WithFileUploads only for upload components.
Do not name Livewire upload methods or properties `upload`; use names like `savePhoto`, `storeMedia`, `attachDocument`, or `saveAvatar`.
Keep upload public properties small: one temporary file or a bounded array of temporary files.
Validate upload type, size, dimensions, and authorization before storage; `accept` attributes are only browser hints.
Use `temporaryUrl()` only for image previews, keep previews thumbnail-sized, and avoid large preview DOM on mobile.
Use `data-loading`, scoped `wire:loading wire:target`, Livewire upload progress events, and `$cancelUpload('property')` where they materially improve slow-3G upload UX.
Store final files through Laravel filesystem APIs or project media services, then persist only path/metadata and reset temporary upload properties.
Do not introduce S3 or `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK` without an explicit infrastructure decision plus config, env, docs, and tests.
Test uploads with `Storage::fake()`, `UploadedFile::fake()`, successful storage assertions, invalid-file assertions, and authorization failures.
Validate every action server-side.
Show friendly validation errors in the active locale.
Use compact DTO arrays for cards.
Use selected columns for all list/card queries.
Use cached lookup values for amenities, rules, countries, and common cities.
Prefer `data-loading` Tailwind variants for Livewire network actions; use `wire:loading` only for simple show/hide indicators.
Use `data-loading:*` on the element that triggers the request, `in-data-loading:*` for child label swaps, `has-data-loading:*` for parent styling, and `peer-data-loading:*` for sibling styling when useful.
Avoid deeply nested `in-data-loading:*` selectors when parent and child components can load at the same time.
Every visible loading label must use translation keys.
Loading states must not replace server-side validation, authorization, idempotency, transactions, or duplicate-submit protection.
Use optimistic UI only where the rollback path is safe and obvious.
Use `lazy` for below-the-fold Livewire child components whose slow data should not block the first render.
Use `defer` for secondary Livewire child components that should load immediately after the first render without waiting for viewport visibility.
For class-based lazy or deferred components, define a `placeholder()` method instead of Blade `@placeholder`; the placeholder root element type must match the final component root element type.
Pass scalar IDs, filters, booleans, and compact strings into lazy/deferred components whenever possible instead of full models or large arrays.
Use `#[Lazy]` or `#[Defer]` only when every usage of the component should be delayed; otherwise prefer instance-level `lazy` or `defer`.
Use `lazy.bundle`, `defer.bundle`, `#[Lazy(bundle: true)]`, or `#[Defer(bundle: true)]` only for many similar components with similar load cost; avoid bundling mixed fast and slow components.
Use `Livewire::withoutLazyLoading()` in tests when assertions need final lazy-loaded content.
Use `@island` for isolated expensive or independent regions inside one component when it improves mobile performance without creating extra child component overhead.
Use `@island(lazy: true)` for below-the-fold expensive regions and `@island(defer: true)` for regions that can load immediately after the first page render.
Use `@placeholder` inside lazy, defer, or skip islands so old phones and slow 3G users see stable loading states.
Do not put `@island` inside Blade loops or conditionals; put loops and conditionals inside the island and expose needed data through component properties or computed properties.
Do not rely on template-local variables inside islands, and never add `@php` to work around island scope. Prepare values in Livewire classes, services, presenters, DTOs, or class-based Blade components.
Use named islands and `wire:island`, `wire:island.append`, or `wire:island.prepend` only for focused update targets such as load-more lists, feeds, counters, and dashboard panels.
Use `always: true` sparingly, only when the island must synchronize with every parent update.
Avoid mutating the same state from the root component and multiple islands at the same time because parallel island requests can race.

## SQLite rules

SQLite is the selected database.
Use migrations for all schema.
Use foreign keys.
Use indexes for every search, filter, join, calendar lookup, booking lookup, and translation lookup.
Use composite indexes for common queries.
Use cursor pagination for large datasets where possible.
Avoid offset pagination for very large search result pages.
Avoid N+1 queries.
Use eager loading with selected columns.
Use query scopes.
Use EXPLAIN QUERY PLAN for critical queries.
Enable WAL mode in local/dev setup documentation where appropriate.
Keep seeders realistic but not enormous by default.

## Localization rules

The app must support at least:
- English: en
- Russian: ru

Every UI string must use translation keys.
No hard-coded visible text in Blade or Livewire components.
Never use `@php`, `@endphp`, or `@php(...)` in Blade templates. Prepare values in Livewire classes, class-based Blade components, presenters, services, or DTO arrays before rendering.
Support future languages without schema rewrites.
Use localized routes or locale middleware.
Store user locale preference.
Allow switching language on mobile.
Use fallback locale when a translation is missing.
Translatable user-generated content must be stored separately from base records.
For listings, rooms, sleeping places, rules, amenities, policies, and help text, support translations per locale.

## Geo data rules

Countries and cities must come from open data sources, not manually typed lists.
Use offline imports into SQLite, not live API calls during search.
Use ISO 3166-compatible country sources.
REST Countries can be used as a convenient country export source when extra fields are needed.
DataHub country-list can be used for a small ISO 3166-1 alpha-2 CSV, but document its ISO licensing note before production use.
Use GeoNames `cities1000` for city autocomplete by default.
Use GeoNames `allCountries` only when the full place catalog is truly needed.
Do not load a map on the first search screen.
Use Natural Earth only if map/country shape data is needed later.
Use Nominatim/OpenStreetMap only for occasional geocoding with respect for usage limits; do not bulk-geocode or mass-import addresses through public Nominatim.

## Friendly UX rules

The system tone must be calm, simple, and helpful.
Avoid scary technical messages.
Every empty state must explain the next action.
Every error must explain how to fix it.
Every booking calculation must be transparent.
Every price must show what is included and what is refundable.
Every rule must be visible before booking.
Every important action must have a confirmation step.

## Feature definition of done

Every new feature must include, when applicable:
- Migration if data is needed
- Model relationships
- English PHPDoc summaries above model methods, with relationship methods explaining the domain purpose of the relation
- Factory
- Seeder if lookup data is introduced
- Livewire class component
- Blade view
- Flux UI
- Mobile-first layout
- Translations for every supported locale
- Validation
- Friendly empty state
- Friendly loading state
- Authorization or policy if needed
- Tests
- Indexes for queries
- Docs update if behavior is important

## Testing rules

Every feature must include tests.
Use feature tests for routes and Livewire components.
Use unit tests for pricing, availability, date calculation, refund calculation, and compatibility scoring.
Use factories and seeders.
Run:
- php artisan test
- ./vendor/bin/pint
- npm run build

## Prohibited for now

Do not build:
- Admin dashboard
- Moderator tools
- Support staff tools
- Finance staff tools
- Cleaner tools
- Property manager tools
- Filament resources
- Livewire Volt components
- Inertia pages
- React/Vue frontend

## Global constraints prompt block

Add this block to the end of almost every implementation prompt:

Global constraints:
- Laravel 13, PHP 8.3+, Livewire 4, Flux Pro, SQLite.
- Do not use Livewire Volt.
- Do not use Filament.
- Do not use Inertia.
- Do not create admin/staff/moderation/support/finance panels.
- Mobile-first, 320px first.
- Must work well on old Android and slow 3G.
- Keep Livewire public properties small.
- Use translations for every visible string.
- Support en and ru from day one.
- Prepare architecture for future languages.
- Add migrations, models, factories, seeders where needed.
- Add Livewire feature tests and unit tests where needed.
- Add indexes for all filtering/search/calendar queries.
- Run php artisan test, Pint, and npm build.
- Update docs when behavior or schema changes.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/flux (FLUXUI_FREE) - v2
- livewire/flux-pro (FLUXUI_PRO) - v2
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files when compatible with this project (for example migrations, models, requests, tests, and Livewire class components). Do not use `make:controller` in this checkout.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
