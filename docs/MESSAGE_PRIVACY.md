# Message Privacy

Guests can view only their own conversations. Hosts can view conversations for their own bookings/listings. Host representatives can view only conversations where they are explicitly added as participants.

Guests do not see:

- internal notes
- host-only attachments
- future-review attachments
- private responsible/contact details
- provider or payment payloads
- access details before disclosure rules allow them

Internal notes are stored in `conversation_internal_notes` and may also appear as `internal_note` messages for host-side workflow continuity. `ConversationPrivacyService::canViewMessage()` hides them from guests.

Attachments are filtered through `ConversationPrivacyService::canViewAttachment()`. `future_review_only` media is hidden from normal UI for both guest and host.
