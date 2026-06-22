# System Events In Chat

System events are displayed inside conversations as messages, but the source of truth is `conversation_system_events`.

Events use translation keys, not stored display text. This keeps English and Russian rendering consistent and prepares the system for future locales.

Important examples:

- booking created or confirmed
- payment required, completed, or failed
- check-in instruction available
- guest arrived
- checkout soon or completed
- deposit review, deduction, or return
- complaint or dispute opened
- maintenance reported or fixed
- cleaning completed
- place ready
- refund created or completed

Events can be normal, important, urgent, or critical. Urgent events update conversation flags and can create notifications.
