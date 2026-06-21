<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\BookingExtensionStatusLog;
use App\Models\BookingStay;
use App\Models\User;

class BookingExtensionStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(BookingExtension $extension, string $newStatus, ?User $user = null, array $context = []): BookingExtension
    {
        if (! $this->canTransition($extension, $newStatus)) {
            return $extension->refresh();
        }

        $oldStatus = $this->statusValue($extension);

        $extension->forceFill([
            'status' => $newStatus,
        ])->save();

        BookingExtensionStatusLog::query()->create([
            'booking_extension_id' => $extension->id,
            'booking_id' => $extension->booking_id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        return $extension->refresh();
    }

    public function canTransition(BookingExtension $extension, string $newStatus): bool
    {
        if ($this->statusValue($extension) === $newStatus) {
            return false;
        }

        return ! in_array($this->statusValue($extension), ['applied', 'closed'], true);
    }

    public function syncBookingStatus(BookingExtension $extension): Booking
    {
        return $extension->booking()->firstOrFail();
    }

    public function syncStayStatus(BookingExtension $extension): ?BookingStay
    {
        return $extension->bookingStay()->first();
    }

    private function statusValue(BookingExtension $extension): string
    {
        return $extension->status instanceof \BackedEnum
            ? (string) $extension->status->value
            : (string) $extension->status;
    }
}
