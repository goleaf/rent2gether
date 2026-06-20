<?php

namespace App\Services\Media;

use App\Models\CheckinRecord;
use App\Models\CheckoutRecord;
use App\Models\Complaint;
use App\Models\Property;
use App\Models\Review;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MediaOwnerResolver
{
    public function resolve(string $ownerType, int $ownerId): Model
    {
        return match ($ownerType) {
            'property' => Property::query()->select(['id', 'host_user_id', 'user_id'])->findOrFail($ownerId),
            'room' => Room::query()->select(['id', 'property_id'])->with(['property:id,host_user_id,user_id'])->findOrFail($ownerId),
            'sleeping_place' => SleepingPlace::query()->select(['id', 'property_id'])->with(['property:id,host_user_id,user_id'])->findOrFail($ownerId),
            'avatar', 'user' => User::query()->select(['id'])->findOrFail($ownerId),
            'complaint' => Complaint::query()->select(['id', 'reporter_id', 'reported_user_id'])->findOrFail($ownerId),
            'checkin' => CheckinRecord::query()->select(['id', 'booking_id'])->with(['booking:id,guest_user_id,host_user_id'])->findOrFail($ownerId),
            'checkout' => CheckoutRecord::query()->select(['id', 'booking_id'])->with(['booking:id,guest_user_id,host_user_id'])->findOrFail($ownerId),
            'review' => Review::query()->select(['id', 'reviewer_id', 'reviewee_id'])->findOrFail($ownerId),
            default => abort(404),
        };
    }

    public function authorize(Model $owner, User $user): void
    {
        $allowed = match (true) {
            $owner instanceof Property => $owner->isOwnedBy($user),
            $owner instanceof Room => $owner->property?->isOwnedBy($user) === true,
            $owner instanceof SleepingPlace => $owner->property?->isOwnedBy($user) === true,
            $owner instanceof User => (int) $owner->id === (int) $user->id,
            $owner instanceof Complaint => in_array((int) $user->id, [(int) $owner->reporter_id, (int) $owner->reported_user_id], true),
            $owner instanceof CheckinRecord => in_array((int) $user->id, [(int) $owner->booking?->guest_user_id, (int) $owner->booking?->host_user_id], true),
            $owner instanceof CheckoutRecord => in_array((int) $user->id, [(int) $owner->booking?->guest_user_id, (int) $owner->booking?->host_user_id], true),
            $owner instanceof Review => in_array((int) $user->id, [(int) $owner->reviewer_id, (int) $owner->reviewee_id], true),
            default => false,
        };

        abort_unless($allowed, 403);
    }

    public function directoryFor(Model $owner, string $collection): string
    {
        $prefix = match (true) {
            $owner instanceof Property => 'properties',
            $owner instanceof Room => 'rooms',
            $owner instanceof SleepingPlace => 'sleeping-places',
            $owner instanceof User => 'avatars',
            $owner instanceof Complaint => 'complaints',
            $owner instanceof CheckinRecord => 'checkins',
            $owner instanceof CheckoutRecord => 'checkouts',
            $owner instanceof Review => 'reviews',
            default => 'media',
        };

        return $prefix.'/'.$owner->getKey().'/'.$collection;
    }
}
