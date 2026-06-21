# Extension vs Relocation

Extension means:

- same property
- same room
- same sleeping place
- new checkout date

Relocation means:

- different sleeping place
- possibly different room or property
- old and new place history must be preserved
- price difference, consent, and availability are handled as relocation

The extension module must not change `sleeping_place_id`.

If the same sleeping place is not available after the current checkout date, the
extension is rejected or blocked. The product can suggest relocation later, but
that is a separate flow with its own audit trail and calendar logic.
