# Booking Request Privacy

Hosts see a safe guest summary, not private documents.

Allowed host view:

- public guest name
- avatar when visible
- city and languages when visible
- rating and completed-stay summary
- verification statuses
- request dates, price, trip purpose, message, and logistics
- warnings and compatibility results

Hidden from hosts:

- guest documents
- passport or document numbers
- exact birth date
- private notes
- saved work or study locations
- raw internal verification metadata
- unconfirmed complaint details

The request host view is built through `BookingRequestPrivacyService` and `UserProfileVisibilityService`.
