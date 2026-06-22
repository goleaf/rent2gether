# Review Mobile Performance

Review UI is mobile-first and keeps Livewire public properties small. Components store IDs, simple filters, and compact state only.

Published review lists use cursor pagination and should load the first 20 reviews. Detailed rating summaries come from snapshots, not raw aggregation queries.

Avoid heavy JavaScript, giant hidden forms, and full media payloads. Review photos should use thumbnails, and older reviews should load by pagination or explicit user action.
