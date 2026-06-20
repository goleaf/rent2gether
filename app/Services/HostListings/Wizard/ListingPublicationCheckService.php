<?php

namespace App\Services\HostListings\Wizard;

use App\Models\ListingPublicationCheck;
use App\Models\Property;
use Illuminate\Support\Collection;

class ListingPublicationCheckService
{
    public function __construct(
        private readonly HostListingReadinessService $readiness,
    ) {}

    public function refreshChecks(Property $property): Collection
    {
        $this->readiness->checkPublishReadiness($property);

        return $property->publicationChecks()
            ->orderByDesc('is_blocking')
            ->orderBy('category')
            ->get();
    }

    public function createOrUpdateCheck(Property $property, array $data): ListingPublicationCheck
    {
        return ListingPublicationCheck::query()->updateOrCreate(
            [
                'property_id' => $property->id,
                'check_key' => $data['check_key'],
                'room_id' => $data['room_id'] ?? null,
                'sleeping_place_id' => $data['sleeping_place_id'] ?? null,
            ],
            [
                'user_id' => $property->host_user_id,
                'category' => $data['category'],
                'severity' => $data['severity'],
                'status' => $data['status'] ?? 'open',
                'message_key' => $data['message_key'],
                'message_params_json' => $data['message_params_json'] ?? [],
                'is_required' => $data['is_required'] ?? false,
                'is_blocking' => $data['is_blocking'] ?? false,
                'fixed_at' => $data['fixed_at'] ?? null,
            ],
        );
    }

    public function markFixed(ListingPublicationCheck $check): ListingPublicationCheck
    {
        $check->forceFill([
            'status' => 'fixed',
            'is_blocking' => false,
            'fixed_at' => now(),
        ])->save();

        return $check->refresh();
    }

    public function getOpenBlockingChecks(Property $property): Collection
    {
        return $property->publicationChecks()
            ->where('status', 'open')
            ->where('is_blocking', true)
            ->get();
    }

    public function getOpenRecommendedChecks(Property $property): Collection
    {
        return $property->publicationChecks()
            ->where('status', 'open')
            ->where('is_blocking', false)
            ->get();
    }
}
