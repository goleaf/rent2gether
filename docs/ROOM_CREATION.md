# Room Creation

`Room` represents a container inside a property. A room can be shared, private, dorm-style, or another supported type, but booking still happens through `SleepingPlace`.

Room comfort details live in `room_comfort_details`. The table stores mobile-search-friendly flags such as desk, lockable door, curtains, noise level, and night work compatibility.

Rooms can be copied or created from `room_templates`. Templates belong to a host and do not introduce manager or staff roles.
