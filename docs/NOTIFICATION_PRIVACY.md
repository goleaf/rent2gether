# Notification Privacy

Notifications must not include sensitive data directly in body text, push payloads, or email payloads.

Hidden data includes:

- door codes
- safe codes
- intercom codes
- exact addresses before disclosure is allowed
- private payment provider payloads
- dispute internals
- host internal notes

Use a neutral notification like “Check-in instructions are available” with an action to open the protected check-in page.

`NotificationPrivacyService::hideSensitivePayload()` removes sensitive keys before notification payloads are shown.
