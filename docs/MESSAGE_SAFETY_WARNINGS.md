# Message Safety Warnings

`ConversationSafetyService` creates soft warnings for risky text without building a moderation or support panel.

MVP warnings include:

- possible off-platform payment
- possible sensitive access details
- possible phone number before allowed
- possible exact address before allowed

Warnings are stored in `conversation_safety_warnings` and can be visible to the sender, recipient, or both. The default is sender-visible only.

The payment warning reminds users to pay inside the service. Access warnings guide hosts toward structured check-in instructions and disclosure logging.
