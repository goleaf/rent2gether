<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;
use App\Models\BookingCancellationPreview;
use App\Models\BookingNoShow;

class BookingNoShowCancellationIntegrationService
{
    public function createCancellationFromNoShow(BookingNoShow $noShow): BookingCancellation
    {
        $preview = $this->createCancellationPreviewFromNoShow($noShow);
        $noShow->loadMissing('host');

        $cancellation = app(BookingCancellationService::class)->confirmCancellation($noShow->host, $preview);
        $cancellation->forceFill(['no_show_case_id' => $noShow->id])->save();

        $noShow->forceFill(['booking_cancellation_id' => $cancellation->id])->save();

        return $cancellation->fresh();
    }

    public function createCancellationPreviewFromNoShow(BookingNoShow $noShow): BookingCancellationPreview
    {
        $noShow->loadMissing(['booking', 'host']);
        app(CancellationPolicySnapshotService::class)->getForBooking($noShow->booking);

        return app(BookingCancellationPreviewService::class)->createPreview($noShow->host, $noShow->booking, [
            'requested_by_type' => 'guest',
            'cancellation_type' => 'no_show_related',
            'reason_key' => 'no_show_related',
            'comment' => $noShow->host_comment,
        ]);
    }
}
