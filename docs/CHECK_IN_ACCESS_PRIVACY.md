# Check-in Access Privacy

Before confirmation, guests must not see exact address, access codes, personal host contact, or sensitive entry instructions.

After confirmation, access is still staged:

- exact address can be visible after booking confirmation or payment rules
- access codes are visible only when access rules and instruction visibility windows allow it
- host or representative contact is shown only to the booking guest

Every sensitive reveal is logged in `booking_check_in_access_disclosures`.

Logged disclosure types:

- exact address
- door code
- intercom code
- key safe code
- host contact
- representative contact
- night entry instruction

Host and guest UI filters must use `BookingCheckInPrivacyService`; provider payloads or internal-only media are not shown in normal UI.
