<?php

namespace App\Services\Notifications;

use App\Models\Booking;
use App\Models\NotificationEvent;

class NotificationEventService
{
    public function __construct(
        private readonly NotificationNumberService $numbers,
        private readonly NotificationTemplateService $templates,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function createEvent(string $eventKey, array $context = []): NotificationEvent
    {
        $template = $this->templates->getByKey($eventKey);

        return NotificationEvent::query()->create([
            'event_number' => $this->numbers->generateEventNumber(),
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'notification_category' => $context['notification_category'] ?? $template?->notification_category ?? 'system',
            'source_type' => $context['source_type'] ?? null,
            'source_id' => $context['source_id'] ?? null,
            'booking_id' => $context['booking_id'] ?? null,
            'booking_stay_id' => $context['booking_stay_id'] ?? null,
            'booking_check_in_id' => $context['booking_check_in_id'] ?? null,
            'booking_check_out_id' => $context['booking_check_out_id'] ?? null,
            'booking_extension_id' => $context['booking_extension_id'] ?? null,
            'booking_relocation_id' => $context['booking_relocation_id'] ?? null,
            'booking_cancellation_id' => $context['booking_cancellation_id'] ?? null,
            'booking_no_show_id' => $context['booking_no_show_id'] ?? null,
            'host_unresponsive_case_id' => $context['host_unresponsive_case_id'] ?? null,
            'listing_mismatch_report_id' => $context['listing_mismatch_report_id'] ?? null,
            'complaint_case_id' => $context['complaint_case_id'] ?? null,
            'dispute_case_id' => $context['dispute_case_id'] ?? null,
            'booking_deposit_id' => $context['booking_deposit_id'] ?? null,
            'maintenance_request_id' => $context['maintenance_request_id'] ?? null,
            'inventory_issue_id' => $context['inventory_issue_id'] ?? null,
            'cleaning_task_id' => $context['cleaning_task_id'] ?? null,
            'inspection_task_id' => $context['inspection_task_id'] ?? null,
            'saved_search_id' => $context['saved_search_id'] ?? null,
            'favorite_id' => $context['favorite_id'] ?? null,
            'waitlist_entry_id' => $context['waitlist_entry_id'] ?? null,
            'property_id' => $context['property_id'] ?? null,
            'room_id' => $context['room_id'] ?? null,
            'sleeping_place_id' => $context['sleeping_place_id'] ?? null,
            'created_by_user_id' => $context['created_by_user_id'] ?? null,
            'payload_json' => $context['payload'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function createBookingEvent(Booking $booking, string $eventKey, array $context = []): NotificationEvent
    {
        $booking->loadMissing(['property', 'room', 'sleepingPlace']);

        return $this->createEvent($eventKey, $context + [
            'booking_id' => $booking->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'source_type' => $context['source_type'] ?? 'booking',
            'source_id' => $context['source_id'] ?? $booking->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function createCheckInEvent(mixed $checkIn, string $eventKey, array $context = []): NotificationEvent
    {
        return $this->createEvent($eventKey, $this->contextFromWorkflow($checkIn, $context) + ['booking_check_in_id' => $checkIn->id ?? null]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function createCheckOutEvent(mixed $checkOut, string $eventKey, array $context = []): NotificationEvent
    {
        return $this->createEvent($eventKey, $this->contextFromWorkflow($checkOut, $context) + ['booking_check_out_id' => $checkOut->id ?? null]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function createDepositEvent(mixed $deposit, string $eventKey, array $context = []): NotificationEvent
    {
        return $this->createEvent($eventKey, $this->contextFromWorkflow($deposit, $context) + ['booking_deposit_id' => $deposit->id ?? null]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function createComplaintEvent(mixed $complaint, string $eventKey, array $context = []): NotificationEvent
    {
        return $this->createEvent($eventKey, $this->contextFromWorkflow($complaint, $context) + ['complaint_case_id' => $complaint->id ?? null]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function createMaintenanceEvent(mixed $request, string $eventKey, array $context = []): NotificationEvent
    {
        return $this->createEvent($eventKey, $this->contextFromWorkflow($request, $context) + ['maintenance_request_id' => $request->id ?? null]);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function contextFromWorkflow(mixed $model, array $context): array
    {
        $booking = $model->booking ?? null;

        return $context + [
            'booking_id' => $model->booking_id ?? $booking?->id,
            'property_id' => $model->property_id ?? $booking?->property_id,
            'room_id' => $model->room_id ?? $booking?->room_id,
            'sleeping_place_id' => $model->sleeping_place_id ?? $booking?->sleeping_place_id,
            'source_type' => $context['source_type'] ?? str(class_basename($model))->snake()->toString(),
            'source_id' => $model->id ?? null,
        ];
    }
}
