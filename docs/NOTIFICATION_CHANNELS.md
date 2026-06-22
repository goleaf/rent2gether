# Notification Channels

The primary MVP channel is `in_app`. It creates records for the notification center, dashboard badges, booking cards, and urgent panels.

`email` can create delivery records when user preferences allow it. A real provider can be attached later.

`sms_future`, `push_future`, and `phone_call_future` are future-ready. They can create structured records but must not send real external messages in the MVP.

`conversation` is represented by a conversation system event when a notification also belongs in booking chat history.
