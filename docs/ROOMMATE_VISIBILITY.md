# Roommate Visibility

Roommate previews are privacy-safe summaries for guests.

Guests may see:

- public name or neutral roommate label
- age range
- gender only when relevant to room policy
- city or country if allowed
- languages
- stay purpose
- sleep schedule
- smoking status
- sociability style
- checkout date

Guests must not see:

- phone
- email
- documents
- exact birth date
- payment data
- host private notes
- complaint history
- internal operational fields

`StayVisibilityService` reads `stay_visibility_preferences` and returns filtered occupant arrays. Public listing summaries should be even more limited than confirmed-booking roommate summaries.
