<?php

namespace App\Services\HostOccupants;

use App\Enums\BookingExtensionStatus;
use App\Enums\ComplaintStatus;
use App\Models\Booking;
use App\Models\HostCleaningTask;
use App\Models\HostGuestStayFlag;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class HostGuestStayFlagService
{
    public function __construct(
        private readonly HostOccupantPrivacyService $privacy,
        private readonly HostCurrentStaySnapshotService $snapshots,
    ) {}

    public function refreshFlagsForBooking(Booking $booking): Collection
    {
        $booking->loadMissing(['extensions:id,booking_id,status', 'complaints:id,booking_id,status']);

        HostGuestStayFlag::query()
            ->where('booking_id', $booking->id)
            ->where('status', 'open')
            ->delete();

        $flags = collect();
        $host = $booking->host;

        if (! $host) {
            return $flags;
        }

        foreach ($this->detectFlagKeys($booking) as $flagKey => $severity) {
            $flags->push($this->createFlag($host, $booking, $flagKey, $severity));
        }

        return $flags;
    }

    public function createFlag(User $host, Booking $booking, string $flagKey, string $severity): HostGuestStayFlag
    {
        if (! $this->privacy->canViewOccupant($host, $booking)) {
            throw new AuthorizationException;
        }

        return HostGuestStayFlag::query()->create([
            'user_id' => $host->id,
            'guest_user_id' => $booking->guest_user_id,
            'booking_id' => $booking->id,
            'flag_key' => $flagKey,
            'status' => 'open',
            'severity' => $severity,
            'message_key' => 'current_occupants.flags.'.$flagKey,
            'message_params_json' => [],
        ]);
    }

    public function resolveFlag(User $host, HostGuestStayFlag $flag): HostGuestStayFlag
    {
        if ((int) $flag->user_id !== (int) $host->id) {
            throw new AuthorizationException;
        }

        $flag->forceFill([
            'status' => 'resolved',
            'resolved_at' => now(),
        ])->save();

        return $flag->refresh();
    }

    public function getOpenFlags(User $host, Booking $booking): Collection
    {
        if (! $this->privacy->canViewOccupant($host, $booking)) {
            throw new AuthorizationException;
        }

        return HostGuestStayFlag::query()
            ->where('user_id', $host->id)
            ->where('booking_id', $booking->id)
            ->where('status', 'open')
            ->orderByDesc('severity')
            ->orderBy('id')
            ->get();
    }

    public function getImportantFlags(User $host): Collection
    {
        return HostGuestStayFlag::query()
            ->where('user_id', $host->id)
            ->where('status', 'open')
            ->whereIn('severity', ['high', 'critical'])
            ->orderByDesc('severity')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<string, string>
     */
    private function detectFlagKeys(Booking $booking): array
    {
        $flags = [];

        if (in_array($this->snapshots->detectPaymentStatus($booking), ['pending', 'partial', 'overdue'], true)) {
            $flags['payment_pending'] = 'medium';
        }

        if ($booking->check_out_date && CarbonImmutable::parse($booking->check_out_date)->isSameDay(CarbonImmutable::today())) {
            $flags['checkout_today'] = 'high';
        }

        if ($booking->check_out_date && CarbonImmutable::parse($booking->check_out_date)->isBefore(CarbonImmutable::today())) {
            $flags['checkout_overdue'] = 'critical';
        }

        if ($this->hasOpenExtension($booking)) {
            $flags['extension_requested'] = 'medium';
        }

        if ($this->hasOpenComplaint($booking)) {
            $flags['complaint_open'] = 'high';
        }

        if ($this->hasOpenCleaningTask($booking)) {
            $flags['cleaning_needed'] = 'medium';
        }

        if (filled($booking->guest_message)) {
            $flags['special_request'] = 'low';
        }

        return $flags;
    }

    private function hasOpenExtension(Booking $booking): bool
    {
        $open = [
            BookingExtensionStatus::AwaitingHostApproval->value,
            BookingExtensionStatus::AwaitingPayment->value,
        ];

        return $booking->extensions
            ->contains(fn ($extension): bool => in_array($this->value($extension->status), $open, true));
    }

    private function hasOpenComplaint(Booking $booking): bool
    {
        $closed = [
            ComplaintStatus::Resolved->value,
            ComplaintStatus::Closed->value,
            ComplaintStatus::Cancelled->value,
            ComplaintStatus::Dismissed->value,
        ];

        return $booking->complaints
            ->contains(fn ($complaint): bool => ! in_array($this->value($complaint->status), $closed, true));
    }

    private function hasOpenCleaningTask(Booking $booking): bool
    {
        return HostCleaningTask::query()
            ->where('booking_id', $booking->id)
            ->whereIn('status', ['planned', 'needed', 'in_progress'])
            ->exists();
    }

    private function value(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return $value === null ? null : (string) $value;
    }
}
