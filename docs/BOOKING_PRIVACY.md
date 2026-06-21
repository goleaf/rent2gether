# Booking Privacy

Guests can view only their own bookings.

Hosts can view only bookings for their own sleeping places.

Guest-facing booking data must not include host payout internals. Host-facing booking data must not expose private guest documents, exact birth date, private notes, saved work or study locations, or internal system checks.

`BookingPrivacyService` provides guest and host filters for safe booking summaries. Host decision screens can show useful booking context, status, dates, price, guest public profile summary, rule compatibility, and warnings, but private verification documents stay hidden.
