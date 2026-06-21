# Stay Compatibility

Stay compatibility helps guests understand room atmosphere without exposing private roommate identities.

`StayCompatibilityService` compares a guest with current room occupancy signals and returns:

- fit score
- translated warning keys

Examples:

- `guest_needs_quiet_but_room_has_late_sleepers`
- `guest_smokes_but_room_non_smoking`
- `guest_wants_private_room_but_room_is_shared`

Warnings must describe the room situation, not a specific person. Use phrases like "the room may be active late" instead of naming an individual resident.

Compatibility is advisory and must not be used as a hidden discrimination mechanism.
