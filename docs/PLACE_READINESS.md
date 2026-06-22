# Place Readiness

`place_readiness_checks` determine whether a `SleepingPlace` is ready for the next guest.

A place is ready only when the required conditions are satisfied:

- checkout completed;
- cleaning completed;
- inspection passed when required;
- repair completed;
- inventory ready;
- access ready;
- no blocking deposit review;
- no blocking complaint;
- calendar can be opened.

Guest UI receives only safe readiness notices, such as “the place is being prepared” or “the place is ready.” Internal notes, responsible contacts, and private media stay hidden.
