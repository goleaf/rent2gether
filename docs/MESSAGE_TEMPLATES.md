# Message Templates

`MessageTemplate` stores quick replies for guests and hosts. Templates are filtered by sender type, conversation type, booking/check-in/check-out requirements, and active state.

Default guest templates cover arrival, delays, keys, check-in problems, host unresponsive flow, extension, late checkout, room problems, Wi-Fi, hot water, towel/bedding, checkout, returned keys, and forgotten items.

Default host templates cover confirmation, unavailable place, check-in instruction, door code, bed location, rules, reply soon, representative help, checkout reminders, keys, locker, review, deposit check, deposit returned, and detail requests.

Some templates can trigger action flows through `MessageTemplateUsageService`, for example:

- host unresponsive
- extension
- checkout
- maintenance
- complaint

Templates never create staff, support, moderator, cleaner, manager, or finance roles.
