<?php

namespace App\Services\HostOccupants\Data;

use App\Models\HostCurrentStaySnapshot;
use Illuminate\Support\Collection;

final readonly class HostOccupantData
{
    /**
     * @param  Collection<int, mixed>  $flags
     * @param  Collection<int, mixed>  $notes
     */
    public function __construct(
        public int $bookingId,
        public int $guestUserId,
        public string $guestDisplayName,
        public ?string $guestAvatarUrl,
        public string $roomLabel,
        public string $sleepingPlaceLabel,
        public string $checkInDate,
        public string $checkOutDate,
        public ?int $nightsLeft,
        public ?string $paymentStatus,
        public ?string $stayStatus,
        public ?string $specialRequestsSummary,
        public bool $hasComplaints,
        public int $openComplaintsCount,
        public bool $needsCheckout,
        public bool $needsExtension,
        public Collection $flags,
        public Collection $notes,
    ) {}

    /**
     * @param  Collection<int, mixed>  $flags
     * @param  Collection<int, mixed>  $notes
     */
    public static function fromSnapshot(HostCurrentStaySnapshot $snapshot, Collection $flags, Collection $notes): self
    {
        return new self(
            bookingId: $snapshot->booking_id,
            guestUserId: $snapshot->guest_user_id,
            guestDisplayName: $snapshot->guest_display_name ?? __('current_occupants.empty.guest'),
            guestAvatarUrl: $snapshot->guest_avatar_url,
            roomLabel: $snapshot->room_label ?? __('current_occupants.empty.room'),
            sleepingPlaceLabel: $snapshot->sleeping_place_label ?? __('current_occupants.empty.sleeping_place'),
            checkInDate: $snapshot->check_in_date->toDateString(),
            checkOutDate: $snapshot->check_out_date->toDateString(),
            nightsLeft: $snapshot->nights_left,
            paymentStatus: $snapshot->payment_status,
            stayStatus: $snapshot->stay_status,
            specialRequestsSummary: $snapshot->special_requests_summary,
            hasComplaints: $snapshot->has_complaints,
            openComplaintsCount: $snapshot->open_complaints_count,
            needsCheckout: $snapshot->needs_checkout,
            needsExtension: $snapshot->needs_extension,
            flags: $flags,
            notes: $notes,
        );
    }
}
