# Rating Aggregates

`rating_aggregates` store metric averages and counts for a target. Snapshot services use aggregates to refresh public summaries.

Listing cards and search should read `sleeping_place_rating_snapshots` and `host_reputation_snapshots`, not raw review tables. This avoids expensive scans and keeps mobile pages fast.

Recalculation is synchronous in MVP and can later be moved behind optional background processing without changing the public contract.
