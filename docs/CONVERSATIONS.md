# Conversations

`Conversation` is the context container for guest-host communication. It can represent a listing inquiry, booking chat, check-in issue, checkout, complaint, dispute, deposit, maintenance, inventory, cleaning/readiness, or a system-only thread.

Each conversation stores the most specific context available:

- `booking_id`
- `property_id`
- `room_id`
- `sleeping_place_id`
- issue-specific IDs such as complaint, dispute, deposit, inventory, cleaning, inspection, or maintenance references

Participants are stored in `conversation_participants`. A participant can be guest, host, host representative, system, or future user. Host representatives are contacts for a specific context, not roles.

Booking conversations should be reused through `ConversationService::getOrCreateForBooking()` to avoid duplicate chat history. Issue-specific processes may create a separate conversation or add system events to the booking conversation.
