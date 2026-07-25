<?php

namespace Tests\Unit;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use BackedEnum;
use Tests\TestCase;

class DomainStatusContractTest extends TestCase
{
    /** @var list<string> */
    private const REQUIRED_AVAILABILITY_STATUSES = [
        'available',
        'occupied',
        'pending_payment',
        'pending_host_confirmation',
        'booked',
        'guest_checked_in',
        'guest_checked_out',
        'closed_by_host',
        'closed_by_service',
        'cleaning',
        'repair',
        'broken',
        'complaint_blocked',
        'hidden',
        'request_only',
    ];

    /** @var list<string> */
    private const REQUIRED_BOOKING_STATUSES = [
        'draft',
        'created',
        'awaiting_host_approval',
        'awaiting_guest_response',
        'awaiting_payment',
        'awaiting_identity_verification',
        'awaiting_document_verification',
        'confirmed',
        'paid',
        'ready_for_check_in',
        'guest_checked_in',
        'in_progress',
        'check_out_soon',
        'guest_checked_out',
        'awaiting_room_inspection',
        'awaiting_deposit_return',
        'completed',
        'awaiting_review',
        'closed',
        'declined_by_host',
        'cancelled_by_guest',
        'cancelled_by_host',
        'cancelled_by_service',
        'unpaid',
        'guest_no_show',
        'host_unresponsive',
        'dispute_opened',
        'frozen_pending_dispute_resolution',
        'requires_support_intervention',
    ];

    public function test_availability_statuses_expose_the_canonical_sleeping_place_calendar_contract(): void
    {
        $this->assertMissingCanonicalValues(
            self::REQUIRED_AVAILABILITY_STATUSES,
            $this->canonicalValues(AvailabilityStatus::class),
            AvailabilityStatus::class,
        );

        $this->assertStatusTranslationsExist('availability', self::REQUIRED_AVAILABILITY_STATUSES);
    }

    public function test_booking_statuses_expose_the_canonical_lifecycle_contract(): void
    {
        $this->assertMissingCanonicalValues(
            self::REQUIRED_BOOKING_STATUSES,
            $this->canonicalValues(BookingStatus::class),
            BookingStatus::class,
        );

        $this->assertStatusTranslationsExist('booking', self::REQUIRED_BOOKING_STATUSES);
    }

    public function test_booking_classification_enums_keep_flow_payment_and_modifiers_separate(): void
    {
        $this->assertBackedEnumContract('App\\Enums\\BookingFlowType', [
            'instant_booking',
            'host_confirmation_booking',
            'stay_request',
            'preliminary_inquiry',
            'long_term_request',
            'urgent_today_booking',
        ], 'booking_flow_type');

        $this->assertBackedEnumContract('App\\Enums\\BookingPaymentMode', [
            'awaiting_payment',
            'with_deposit',
            'without_deposit',
            'partial_payment',
            'full_payment',
        ], 'booking_payment_mode');

        $this->assertBackedEnumContract('App\\Enums\\BookingModifier', [
            'extension',
            'relocation',
            'group_booking',
            'two_guest_sleeping_place',
        ], 'booking_modifier');
    }

    /**
     * @param  class-string<BackedEnum>  $enumClass
     * @return list<string>
     */
    private function canonicalValues(string $enumClass): array
    {
        return array_values(array_unique(array_map(
            fn (BackedEnum $case): string => method_exists($case, 'canonicalValue')
                ? $case->canonicalValue()
                : (string) $case->value,
            $enumClass::cases(),
        )));
    }

    /**
     * @param  list<string>  $requiredValues
     * @param  list<string>  $actualValues
     */
    private function assertMissingCanonicalValues(array $requiredValues, array $actualValues, string $label): void
    {
        $missing = array_values(array_diff($requiredValues, $actualValues));

        $this->assertSame([], $missing, $label.' is missing canonical values: '.implode(', ', $missing));
    }

    /**
     * @param  list<string>  $values
     */
    private function assertBackedEnumContract(string $enumClass, array $values, string $translationGroup): void
    {
        $this->assertTrue(enum_exists($enumClass), "{$enumClass} enum is missing.");
        $this->assertTrue(is_subclass_of($enumClass, BackedEnum::class), "{$enumClass} must be a backed enum.");

        /** @var class-string<BackedEnum> $enumClass */
        $this->assertMissingCanonicalValues($values, $this->canonicalValues($enumClass), $enumClass);
        $this->assertStatusTranslationsExist($translationGroup, $values);

        foreach ($enumClass::cases() as $case) {
            $this->assertTrue(method_exists($case, 'label'), $enumClass.'::'.$case->name.' is missing label().');
        }
    }

    /**
     * @param  list<string>  $values
     */
    private function assertStatusTranslationsExist(string $group, array $values): void
    {
        foreach (['en', 'ru'] as $locale) {
            foreach ($values as $value) {
                $label = __("statuses.{$group}.{$value}", [], $locale);

                $this->assertNotSame(
                    "statuses.{$group}.{$value}",
                    $label,
                    "{$locale} translation missing for statuses.{$group}.{$value}",
                );
                $this->assertNotSame('', trim((string) $label));
            }
        }
    }
}
