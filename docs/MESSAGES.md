# Messages

`ConversationMessage` stores chat messages, quick-template messages, system events, attachments/cards, and internal notes.

User messages keep the original body and locale. System messages store `translation_key` and `translation_params_json`, so each viewer sees the event in their active locale.

Messages are linked back to booking and place context:

- `booking_id`
- `property_id`
- `room_id`
- `sleeping_place_id`
- `source_type`
- `source_id`

The MVP loads the latest 30 messages first and leaves older history to cursor/load-more UI. Internal notes are created as messages for host workflow continuity, but they are hidden from guests by `ConversationPrivacyService`.
