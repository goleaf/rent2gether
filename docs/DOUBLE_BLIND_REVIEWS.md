# Double-Blind Reviews

Double-blind publishing protects honest feedback. When enabled, a guest review or host review is stored as `waiting_other_party` until both sides submit or the review window expires.

When both sides submit, `ReviewPublishingService::publishPairIfReady()` publishes the pair, creates rating events, and refreshes rating snapshots. If the review window expires first, `publishAfterWindowExpired()` publishes submitted reviews and marks them as published after the window.

Roommate experience reviews follow the same privacy posture and should be shown publicly only after publication.
