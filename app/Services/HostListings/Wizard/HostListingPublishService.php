<?php

namespace App\Services\HostListings\Wizard;

use App\Models\Property;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HostListingPublishService
{
    public function __construct(
        private readonly HostListingReadinessService $readiness,
        private readonly HostListingStatusService $statuses,
    ) {}

    public function requestPublication(User $host, Property $property, ?string $comment = null): Property
    {
        $this->authorizeHost($host, $property);

        $readiness = $this->readiness->checkPublishReadiness($property);

        if (! $readiness['ready']) {
            $this->statuses->markIncomplete($property);

            throw ValidationException::withMessages([
                'publication' => __('listing_wizard.errors.not_ready'),
            ]);
        }

        return DB::transaction(function () use ($property, $comment): Property {
            $property->forceFill([
                'publication_status' => 'pending_review',
                'review_status' => 'pending',
                'review_requested_at' => now(),
                'reviewed_at' => null,
                'review_comment' => $comment,
                'rejection_reason' => null,
            ])->save();

            $property->rooms()->update(['publication_status' => 'pending_review']);
            $property->sleepingPlaces()->update(['publication_status' => 'pending_review']);

            return $property->fresh();
        });
    }

    public function publishIfReady(User $host, Property $property): Property
    {
        $this->authorizeHost($host, $property);

        $readiness = $this->readiness->checkPublishReadiness($property);

        if (! $readiness['ready']) {
            $this->statuses->markIncomplete($property);

            throw ValidationException::withMessages([
                'publication' => __('listing_wizard.errors.not_ready'),
            ]);
        }

        return $this->statuses->markPublished($property);
    }

    public function pause(User $host, Property $property): Property
    {
        $this->authorizeHost($host, $property);
        $property->forceFill(['publication_status' => 'paused', 'paused_at' => now()])->save();

        return $property->refresh();
    }

    public function hide(User $host, Property $property): Property
    {
        $this->authorizeHost($host, $property);
        $property->forceFill(['publication_status' => 'hidden'])->save();

        return $property->refresh();
    }

    public function archive(User $host, Property $property): Property
    {
        $this->authorizeHost($host, $property);
        $property->forceFill(['publication_status' => 'archived', 'archived_at' => now()])->save();

        return $property->refresh();
    }

    public function rejectAutomatically(Property $property, array $blockingIssues): Property
    {
        $property->forceFill([
            'publication_status' => 'rejected',
            'review_status' => 'auto_rejected',
            'rejection_reason' => collect($blockingIssues)->pluck('check_key')->join(', '),
            'reviewed_at' => now(),
        ])->save();

        return $property->refresh();
    }

    private function authorizeHost(User $host, Property $property): void
    {
        if (! $property->isOwnedBy($host)) {
            throw new AuthorizationException;
        }
    }
}
