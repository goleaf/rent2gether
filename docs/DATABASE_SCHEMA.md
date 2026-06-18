# Database Schema

## Core hierarchy

- users
- user_profiles
- host_profiles
- guest_preferences
- countries
- regions
- cities
- properties
- rooms
- sleeping_places
- availability_days
- price_rules
- discount_rules
- bookings
- booking_guests
- booking_price_lines
- booking_status_histories
- payment_records
- deposit_records
- refund_requests
- favorites
- saved_searches
- waitlist_items
- message_threads
- messages
- reviews
- complaints
- notifications
- user_settings
- locale_settings

## Notes

- Translation tables use locale-specific rows.
- Availability is stored per sleeping place.
- Booking overlap protection must be enforced at the application layer and backed by indexes.

