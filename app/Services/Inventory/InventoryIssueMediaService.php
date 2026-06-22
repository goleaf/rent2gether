<?php

namespace App\Services\Inventory;

use App\Models\InventoryIssue;
use App\Models\InventoryIssueMedia;
use App\Models\User;
use Illuminate\Support\Collection;

class InventoryIssueMediaService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function uploadIssueEvidence(User $user, InventoryIssue $issue, array $data): InventoryIssueMedia
    {
        return $this->createMedia($user, $issue, array_merge($data, [
            'media_role' => $data['media_role'] ?? 'issue_evidence',
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function uploadReplacementReceipt(User $host, InventoryIssue $issue, array $data): InventoryIssueMedia
    {
        return $this->createMedia($host, $issue, array_merge($data, [
            'media_type' => $data['media_type'] ?? 'receipt',
            'media_role' => 'replacement_receipt',
            'visibility' => $data['visibility'] ?? 'host_only',
        ]));
    }

    /**
     * @return Collection<int, InventoryIssueMedia>
     */
    public function getVisibleMedia(User $user, InventoryIssue $issue): Collection
    {
        return $issue->media()
            ->orderByDesc('id')
            ->get()
            ->filter(fn (InventoryIssueMedia $media): bool => app(InventoryPrivacyService::class)->canViewIssueMedia($user, $media))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createMedia(User $user, InventoryIssue $issue, array $data): InventoryIssueMedia
    {
        return InventoryIssueMedia::query()->create([
            'inventory_issue_id' => $issue->id,
            'booking_id' => $issue->booking_id,
            'uploaded_by_user_id' => $user->id,
            'media_type' => $data['media_type'] ?? 'photo',
            'media_role' => $data['media_role'],
            'path' => $data['path'],
            'thumbnail_path' => $data['thumbnail_path'] ?? null,
            'caption' => $data['caption'] ?? null,
            'visibility' => $data['visibility'] ?? 'guest_and_host',
        ]);
    }
}
