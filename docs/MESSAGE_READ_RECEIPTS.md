# Message Read Receipts

Read receipts are stored in `conversation_message_reads`.

The system also keeps fast counters on the conversation:

- `guest_unread_count`
- `host_unread_count`

`ConversationReadService::markMessageRead()` stores a receipt, updates participant `last_read_message_id` and `last_read_at`, and syncs unread counters. `markConversationRead()` marks every visible message in the conversation for the current user.

Read receipts support:

- urgent-message accountability
- check-in instruction visibility
- deposit or complaint evidence
- response-time calculations

System messages and internal notes should not count as user response events.
