# Cleaning Checklists

Cleaning checklist rows live in `cleaning_task_items`.

Default items cover bedding, towels, dust, trash, shared spaces, locker, bed, mattress, socket, lamp, privacy curtain, storage hooks, ventilation, smells, mold, insects, and after photos.

Statuses:

- `pending`
- `completed`
- `skipped`
- `failed`
- `not_required`

If a policy requires checklist completion, `CleaningTaskService::markCompleted()` refuses to complete the task while required items remain incomplete. The user receives translated validation messages.
