# Notifications

Notifications start from a `notification_event`. The event records the domain context: booking, property, room, sleeping place, source type, and source id when available.

The event can create one or more user notifications for the guest, host, or an explicitly allowed host representative. The notification keeps the action, urgency, expiry, read state, and safe translation keys for rendering in the notification center.

In-app notifications are the MVP channel. Email, SMS, push, and phone delivery records are structured for future providers, but future channels do not send real messages yet.

Notifications must never expose sensitive payloads such as access codes, exact addresses before disclosure is allowed, provider/payment payloads, or internal notes. They should link to the protected page where the user can view details safely.
