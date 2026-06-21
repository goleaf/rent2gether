# Cancellation Calendar Release

Before check-in, confirmed cancellation can release active `sleeping_place_booking_date_locks` for the booking. The sleeping place may become available again if no other lock or block exists.

After check-in, cancellation must not open the sleeping place immediately. The cancellation uses `calendar_release_status = kept_blocked` until checkout, inspection, cleaning, and any repair or complaint handling make the place ready.

Waitlist, saved-search, and favorite-place notifications are integration services. They can record events now and may be connected to richer flows later without adding support or staff panels.
