# Host Unresponsive Privacy

Guests can view only their own host-unresponsive cases. Hosts can view only cases for their own bookings.

Future support fields exist for later review workflows, but they are hidden from normal UI and do not create support, staff, finance, moderator, manager, or admin roles.

Media visibility is enforced by `HostUnresponsivePrivacyService`: shared media is visible to guest and host, guest-only media only to the guest, host-only media only to the host, and internal/future-support media is hidden from normal users.

Provider/private payment data is never exposed through host-unresponsive case filters.
