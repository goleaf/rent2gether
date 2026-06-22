<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use App\Livewire\Complaints\GuestComplaintForm;
use App\Livewire\Disputes\GuestDisputeDetailsPage;
use App\Livewire\Host\Complaints\HostComplaintDetailsSheet;
use App\Livewire\Host\Disputes\HostDisputeDetailsSheet;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutIssue;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\BookingListingMismatchReport;
use App\Models\BookingNoShow;
use App\Models\BookingRefund;
use App\Models\BookingStay;
use App\Models\ComplaintEvidence;
use App\Models\ComplaintResolutionOption;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Complaints\ComplaintActionService;
use App\Services\Complaints\ComplaintCancellationIntegrationService;
use App\Services\Complaints\ComplaintCaseService;
use App\Services\Complaints\ComplaintCleaningIntegrationService;
use App\Services\Complaints\ComplaintDepositIntegrationService;
use App\Services\Complaints\ComplaintEvidenceService;
use App\Services\Complaints\ComplaintHostUnresponsiveIntegrationService;
use App\Services\Complaints\ComplaintMaintenanceIntegrationService;
use App\Services\Complaints\ComplaintMismatchIntegrationService;
use App\Services\Complaints\ComplaintNoShowIntegrationService;
use App\Services\Complaints\ComplaintPrivacyService;
use App\Services\Complaints\ComplaintRatingIntegrationService;
use App\Services\Complaints\ComplaintRefundIntegrationService;
use App\Services\Complaints\ComplaintRelocationIntegrationService;
use App\Services\Complaints\ComplaintResolutionService;
use App\Services\Complaints\ComplaintResponseService;
use App\Services\Complaints\ComplaintSearchIntegrationService;
use App\Services\Disputes\DisputeCaseService;
use App\Services\Disputes\DisputeDecisionService;
use App\Services\Disputes\DisputeEvidenceService;
use App\Services\Disputes\DisputeFreezeService;
use App\Services\Disputes\DisputeMessageService;
use App\Services\Disputes\DisputePrivacyService;
use App\Services\Disputes\DisputeProposalService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ComplaintDisputeFlowPointNineteenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-07-12 10:00:00');
        CarbonImmutable::setTestNow('2026-07-12 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_point_nineteen_schema_exists(): void
    {
        foreach ([
            'complaint_cases',
            'complaint_parties',
            'complaint_evidence',
            'complaint_responses',
            'complaint_resolution_options',
            'complaint_actions',
            'dispute_cases',
            'dispute_evidence',
            'dispute_messages',
            'dispute_resolution_proposals',
            'dispute_decisions',
            'complaint_status_logs',
            'complaint_events',
            'dispute_status_logs',
            'dispute_events',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table.' table is missing.');
        }

        $this->assertTrue(Schema::hasColumn('complaint_cases', 'future_review_required'));
        $this->assertTrue(Schema::hasColumn('complaint_cases', 'dispute_case_id'));
        $this->assertTrue(Schema::hasColumn('dispute_cases', 'refund_frozen'));
        $this->assertTrue(Schema::hasColumn('dispute_cases', 'mismatch_report_id'));
    }

    public function test_guest_and_host_can_create_contextual_complaints_with_parties_and_notifications(): void
    {
        [$guest, $host, , $booking] = $this->createActiveBooking();
        $otherGuest = User::factory()->create();
        $otherHost = User::factory()->host()->create();

        $guestCase = app(ComplaintCaseService::class)->createFromGuest($guest, $booking, [
            'complaint_type' => 'dirty_room',
            'severity' => 'high',
            'description' => 'The room is dirty and smells bad.',
            'desired_resolution_type' => 'cleaning',
            'guest_wants_refund' => true,
        ]);

        $hostCase = app(ComplaintCaseService::class)->createFromHost($host, $booking, [
            'complaint_type' => 'guest_rule_violation',
            'against_type' => 'guest',
            'description' => 'Guest smoked in a non-smoking room.',
            'desired_resolution_type' => 'deposit_deduction',
            'host_wants_deposit_deduction' => true,
        ]);

        $guestCase = $guestCase->fresh(['parties']);

        $this->assertSame($booking->id, $guestCase->booking_id);
        $this->assertSame($booking->property_id, $guestCase->property_id);
        $this->assertSame($booking->room_id, $guestCase->room_id);
        $this->assertSame($booking->sleeping_place_id, $guestCase->sleeping_place_id);
        $this->assertSame('guest', $guestCase->submitted_by_type);
        $this->assertSame('host', $guestCase->against_type);
        $this->assertCount(2, $guestCase->parties);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $host->id,
            'type' => 'complaint_case_submitted',
        ]);
        $this->assertSame('host', $hostCase->submitted_by_type);
        $this->assertSame($guest->id, $hostCase->against_user_id);

        $privacy = app(ComplaintPrivacyService::class);
        $this->assertTrue($privacy->canGuestView($guest, $guestCase));
        $this->assertTrue($privacy->canHostView($host, $guestCase));
        $this->assertFalse($privacy->canGuestView($otherGuest, $guestCase));
        $this->assertFalse($privacy->canHostView($otherHost, $guestCase));
        $this->assertArrayNotHasKey('future_review_comment', $privacy->filterForGuest($guest, $guestCase));
        $this->assertArrayNotHasKey('future_review_comment', $privacy->filterForHost($host, $guestCase));

        $this->expectException(ValidationException::class);
        app(ComplaintCaseService::class)->createFromGuest($otherGuest, $booking, [
            'complaint_type' => 'dirty_room',
            'description' => 'Wrong guest.',
        ]);
    }

    public function test_evidence_response_resolution_and_integrations_work_without_forcing_dispute(): void
    {
        [$guest, $host, $place, $booking] = $this->createActiveBooking();
        $alternativePlace = SleepingPlace::factory()
            ->for($place->room)
            ->for($place->property)
            ->create(['base_price_per_night' => 45, 'base_price' => 45]);

        $case = app(ComplaintCaseService::class)->createFromGuest($guest, $booking, [
            'complaint_type' => 'dirty_room',
            'severity' => 'emergency',
            'description' => 'Unsafe dirt in the room.',
            'desired_resolution_type' => 'cleaning',
            'guest_wants_relocation' => true,
            'guest_wants_refund' => true,
        ]);

        $guestEvidence = app(ComplaintEvidenceService::class)->uploadEvidence($guest, $case, [
            'evidence_type' => 'photo',
            'evidence_role' => 'dirty_place_photo',
            'path' => 'complaints/dirty-room.jpg',
            'visibility' => 'guest_and_host',
        ]);
        $internalEvidence = ComplaintEvidence::factory()->create([
            'complaint_case_id' => $case->id,
            'booking_id' => $booking->id,
            'uploaded_by_user_id' => $guest->id,
            'visibility' => 'internal',
        ]);

        app(ComplaintResponseService::class)->acceptProblem($host, $case, 'I will solve it.');
        app(ComplaintResponseService::class)->denyProblem($host, $case, 'I disagree.');
        app(ComplaintResponseService::class)->askForEvidence($host, $case, 'Please add one more photo.');
        app(ComplaintResponseService::class)->offerResolution($host, $case, 'cleaning', ['amount' => 0]);
        app(ComplaintResponseService::class)->offerResolution($host, $case, 'repair', []);
        app(ComplaintResponseService::class)->offerResolution($host, $case, 'relocation', ['sleeping_place_id' => $alternativePlace->id]);
        $refundResponse = app(ComplaintResponseService::class)->offerResolution($host, $case, 'partial_refund', ['amount' => 25]);

        $refundOption = ComplaintResolutionOption::query()
            ->where('complaint_case_id', $case->id)
            ->where('resolution_type', 'partial_refund')
            ->firstOrFail();

        app(ComplaintResolutionService::class)->rejectResolution($guest, $refundOption);
        app(ComplaintResolutionService::class)->acceptResolution($guest, $refundOption);
        $cleaning = app(ComplaintCleaningIntegrationService::class)->createCleaningFromComplaint($case->fresh());
        $maintenance = app(ComplaintMaintenanceIntegrationService::class)->createMaintenanceFromComplaint($case->fresh());
        $relocation = app(ComplaintRelocationIntegrationService::class)->createRelocationFromComplaint($case->fresh(), $alternativePlace);
        $cancellationPreview = app(ComplaintCancellationIntegrationService::class)->createCancellationPreviewFromComplaint($case->fresh());
        $refund = app(ComplaintRefundIntegrationService::class)->createRefundFromComplaint($case->fresh(), 25);
        $depositCase = app(ComplaintDepositIntegrationService::class)->createDepositCaseFromComplaint($case->fresh());
        app(ComplaintActionService::class)->createAction($case->fresh(), 'request_more_evidence', ['created_by_user_id' => $host->id]);

        $visibleEvidence = app(ComplaintEvidenceService::class)->getVisibleEvidence($host, $case->fresh());
        $privacy = app(ComplaintPrivacyService::class);

        $this->assertSame('offer_refund', $refundResponse->response_type);
        $this->assertTrue($privacy->canViewEvidence($host, $guestEvidence));
        $this->assertFalse($privacy->canViewEvidence($guest, $internalEvidence));
        $this->assertTrue($visibleEvidence->contains('id', $guestEvidence->id));
        $this->assertFalse($visibleEvidence->contains('id', $internalEvidence->id));
        $this->assertSame($cleaning->id, $case->fresh()->cleaning_task_id);
        $this->assertNotNull($maintenance);
        $this->assertSame($relocation->id, $case->fresh()->booking_relocation_id);
        $this->assertSame('complaint_related', $cancellationPreview->cancellation_type);
        $this->assertSame($refund->id, $case->fresh()->booking_refund_id);
        $this->assertNotNull($depositCase);
        $this->assertDatabaseHas('complaint_actions', [
            'complaint_case_id' => $case->id,
            'action_type' => 'create_refund',
            'status' => 'completed',
        ]);
    }

    public function test_complaints_can_be_created_from_related_booking_flows(): void
    {
        [$guest, $host, , $booking, $checkIn, $stay, $checkOut] = $this->createActiveBooking();
        $mismatch = BookingListingMismatchReport::factory()->create([
            'booking_id' => $booking->id,
            'booking_stay_id' => $stay->id,
            'booking_check_in_id' => $checkIn->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'mismatch_type' => 'missing_wifi',
            'severity' => 'high',
        ]);
        $checkInProblem = $checkIn->problems()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'problem_type' => 'host_not_answering',
            'severity' => 'high',
            'status' => 'reported',
            'description' => 'Host did not answer.',
            'reported_at' => now(),
        ]);
        $checkoutIssue = BookingCheckOutIssue::factory()->create([
            'booking_check_out_id' => $checkOut->id,
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'issue_type' => 'damage',
            'severity' => 'high',
        ]);
        $noShow = BookingNoShow::factory()->create([
            'booking_id' => $booking->id,
            'booking_check_in_id' => $checkIn->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'status' => 'dispute_opened',
        ]);
        $hostUnresponsive = BookingHostUnresponsiveCase::factory()->create([
            'booking_id' => $booking->id,
            'booking_check_in_id' => $checkIn->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'status' => 'unresolved',
        ]);

        $fromMismatch = app(ComplaintCaseService::class)->createFromMismatch($mismatch);
        $fromCheckIn = app(ComplaintCaseService::class)->createFromCheckInProblem($checkInProblem);
        $fromCheckout = app(ComplaintCaseService::class)->createFromCheckoutIssue($checkoutIssue);
        $fromNoShow = app(ComplaintCaseService::class)->createFromNoShow($noShow);
        $fromHostUnresponsive = app(ComplaintCaseService::class)->createFromHostUnresponsive($hostUnresponsive);

        app(ComplaintMismatchIntegrationService::class)->linkMismatch($fromMismatch, $mismatch);
        app(ComplaintNoShowIntegrationService::class)->linkNoShow($fromNoShow, $noShow);
        app(ComplaintHostUnresponsiveIntegrationService::class)->linkHostUnresponsive($fromHostUnresponsive, $hostUnresponsive);

        $this->assertSame('listing_mismatch', $fromMismatch->complaint_type);
        $this->assertSame('check_in_problem', $fromCheckIn->source_type);
        $this->assertSame('checkout_issue', $fromCheckout->source_type);
        $this->assertSame('no_show', $fromNoShow->source_type);
        $this->assertSame('host_unresponsive', $fromHostUnresponsive->source_type);
        $this->assertSame($fromMismatch->id, $mismatch->fresh()->complaint_case_id);
        $this->assertDatabaseHas('complaint_events', [
            'complaint_case_id' => $fromHostUnresponsive->id,
            'event_key' => 'complaint_submitted',
        ]);
    }

    public function test_dispute_flow_freezes_related_effects_and_records_mutual_agreement(): void
    {
        [$guest, $host, , $booking] = $this->createActiveBooking();
        $case = app(ComplaintCaseService::class)->createFromGuest($guest, $booking, [
            'complaint_type' => 'deposit_problem',
            'severity' => 'high',
            'description' => 'Deposit should be returned.',
            'desired_resolution_type' => 'deposit_return',
            'amount_requested' => 50,
        ]);

        $dispute = app(DisputeCaseService::class)->openFromComplaint($guest, $case, [
            'dispute_type' => 'deposit_dispute',
            'amount_disputed' => 50,
            'description' => 'Guest and host do not agree.',
        ]);
        app(DisputeFreezeService::class)->freezeRefundIfNeeded($dispute);
        app(DisputeFreezeService::class)->freezeDepositIfNeeded($dispute->fresh());
        app(DisputeFreezeService::class)->freezeHostPayoutIfNeeded($dispute->fresh());
        app(DisputeFreezeService::class)->freezeRatingImpactIfNeeded($dispute->fresh());
        app(DisputeEvidenceService::class)->uploadEvidence($guest, $dispute->fresh(), [
            'evidence_type' => 'screenshot',
            'evidence_role' => 'deposit_evidence',
            'path' => 'disputes/deposit.png',
        ]);
        app(DisputeMessageService::class)->sendMessage($host, $dispute->fresh(), 'I propose a partial return.');
        $proposal = app(DisputeProposalService::class)->createProposal($guest, $dispute->fresh(), [
            'resolution_type' => 'deposit_return_partial',
            'amount' => 25,
            'description' => 'Return half.',
        ]);
        app(DisputeProposalService::class)->acceptProposal($guest, $proposal);
        $acceptedByBoth = app(DisputeProposalService::class)->acceptProposal($host, $proposal->fresh());
        $decision = app(DisputeDecisionService::class)->recordMutualAgreement($dispute->fresh(), $acceptedByBoth->fresh());
        app(DisputeDecisionService::class)->applyDecision($decision->fresh());
        app(DisputeFreezeService::class)->releaseFreezesAfterResolution($dispute->fresh());

        $dispute = $dispute->fresh(['decisions']);
        $case = $case->fresh();

        $this->assertTrue((bool) $case->has_dispute);
        $this->assertSame($dispute->id, $case->dispute_case_id);
        $this->assertSame('accepted_by_both', $acceptedByBoth->status);
        $this->assertSame('mutual_agreement', $decision->decision_type);
        $this->assertFalse((bool) $dispute->booking_frozen);
        $this->assertFalse((bool) $dispute->refund_frozen);
        $this->assertDatabaseHas('dispute_messages', [
            'dispute_case_id' => $dispute->id,
            'message_type' => 'statement',
        ]);
        $this->assertDatabaseHas('dispute_events', [
            'dispute_case_id' => $dispute->id,
            'event_key' => 'mutual_agreement_reached',
        ]);
    }

    public function test_disputes_can_open_from_money_and_exception_sources_and_privacy_filters_internal_data(): void
    {
        [$guest, $host, , $booking, $checkIn] = $this->createActiveBooking();
        $refund = BookingRefund::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'amount' => 30,
            'currency' => 'EUR',
        ]);
        $noShow = BookingNoShow::factory()->create([
            'booking_id' => $booking->id,
            'booking_check_in_id' => $checkIn->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
        ]);
        $hostUnresponsive = BookingHostUnresponsiveCase::factory()->create([
            'booking_id' => $booking->id,
            'booking_check_in_id' => $checkIn->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
        ]);

        $fromRefund = app(DisputeCaseService::class)->openFromRefund($guest, $refund, ['dispute_type' => 'refund_dispute']);
        $fromDeposit = app(DisputeCaseService::class)->openFromDeposit($guest, (object) [
            'id' => 777,
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'amount' => 50,
        ], ['dispute_type' => 'deposit_dispute']);
        $fromNoShow = app(DisputeCaseService::class)->openFromNoShow($guest, $noShow, ['dispute_type' => 'no_show_dispute']);
        $fromHostUnresponsive = app(DisputeCaseService::class)->openFromHostUnresponsive($guest, $hostUnresponsive, ['dispute_type' => 'host_unresponsive_dispute']);

        $evidence = app(DisputeEvidenceService::class)->uploadEvidence($guest, $fromRefund, [
            'evidence_type' => 'payment_record',
            'evidence_role' => 'refund_evidence',
            'source_type' => 'booking_refund',
            'source_id' => $refund->id,
        ]);
        $internalEvidence = app(DisputeEvidenceService::class)->uploadEvidence($host, $fromRefund->fresh(), [
            'evidence_type' => 'system_event',
            'evidence_role' => 'other',
            'visibility' => 'internal',
        ]);

        $privacy = app(DisputePrivacyService::class);

        $this->assertSame($refund->id, $fromRefund->booking_refund_id);
        $this->assertSame(777, $fromDeposit->deposit_case_id);
        $this->assertSame($noShow->id, $fromNoShow->booking_no_show_id);
        $this->assertSame($hostUnresponsive->id, $fromHostUnresponsive->host_unresponsive_case_id);
        $this->assertTrue($privacy->canGuestView($guest, $fromRefund));
        $this->assertTrue($privacy->canHostView($host, $fromRefund));
        $this->assertTrue($privacy->canViewEvidence($guest, $evidence));
        $this->assertFalse($privacy->canViewEvidence($guest, $internalEvidence));
        $this->assertArrayNotHasKey('future_decision_comment', $privacy->filterForGuest($guest, $fromRefund));
    }

    public function test_rating_search_and_livewire_surfaces_are_translated(): void
    {
        [$guest, $host, $place, $booking] = $this->createActiveBooking();
        $case = app(ComplaintCaseService::class)->createFromGuest($guest, $booking, [
            'complaint_type' => 'unsafe_situation',
            'severity' => 'emergency',
            'description' => 'Unsafe situation.',
            'desired_resolution_type' => 'relocation',
        ]);
        $dispute = app(DisputeCaseService::class)->openFromComplaint($guest, $case, [
            'dispute_type' => 'safety_dispute',
            'amount_disputed' => 0,
        ]);

        app(ComplaintRatingIntegrationService::class)->recordConfirmedComplaint($case->fresh());
        app(ComplaintRatingIntegrationService::class)->recordResolvedComplaint($case->fresh());
        app(ComplaintRatingIntegrationService::class)->removeRatingImpactIfRejected($case->fresh());
        app(ComplaintSearchIntegrationService::class)->markPlaceRequestOnlyIfSerious($case->fresh());
        app(ComplaintSearchIntegrationService::class)->hidePlaceIfUnsafe($case->fresh());
        app(ComplaintSearchIntegrationService::class)->createHostSuggestions($case->fresh());

        $this->assertSame('hidden', $place->fresh()->status->value);
        $this->assertDatabaseHas('host_listing_suggestions', [
            'user_id' => $host->id,
            'sleeping_place_id' => $place->id,
            'suggestion_key' => 'complaint_update_listing',
        ]);
        $this->assertDatabaseHas('complaint_events', [
            'complaint_case_id' => $case->id,
            'event_key' => 'complaint_resolved',
        ]);

        app()->setLocale('en');

        Livewire::actingAs($guest)
            ->test(GuestComplaintForm::class, ['booking' => $booking])
            ->assertSee(__('complaints.title'))
            ->assertSee(__('complaints.actions.submit_complaint'));
        Livewire::actingAs($host)
            ->test(HostComplaintDetailsSheet::class, ['case' => $case])
            ->assertSee(__('complaints.host_title'))
            ->assertSee(__('complaints.actions.offer_relocation'));
        Livewire::actingAs($guest)
            ->test(GuestDisputeDetailsPage::class, ['dispute' => $dispute])
            ->assertSee(__('disputes.title'))
            ->assertSee(__('disputes.actions.create_proposal'));
        Livewire::actingAs($host)
            ->test(HostDisputeDetailsSheet::class, ['dispute' => $dispute])
            ->assertSee(__('disputes.host_title'))
            ->assertSee(__('disputes.actions.accept_proposal'));

        app()->setLocale('ru');

        Livewire::actingAs($guest)
            ->test(GuestComplaintForm::class, ['booking' => $booking])
            ->assertSee(__('complaints.title'))
            ->assertSee(__('complaints.actions.submit_complaint'));
        Livewire::actingAs($host)
            ->test(HostComplaintDetailsSheet::class, ['case' => $case])
            ->assertSee(__('complaints.host_title'))
            ->assertSee(__('complaints.actions.offer_relocation'));
        Livewire::actingAs($guest)
            ->test(GuestDisputeDetailsPage::class, ['dispute' => $dispute])
            ->assertSee(__('disputes.title'))
            ->assertSee(__('disputes.actions.create_proposal'));
        Livewire::actingAs($host)
            ->test(HostDisputeDetailsSheet::class, ['dispute' => $dispute])
            ->assertSee(__('disputes.host_title'))
            ->assertSee(__('disputes.actions.accept_proposal'));

        $this->expectException(AuthorizationException::class);
        app(DisputeMessageService::class)->sendMessage(User::factory()->create(), $dispute, 'No access.');
    }

    /**
     * @return array{0:User, 1:User, 2:SleepingPlace, 3:Booking, 4:BookingCheckIn, 5:BookingStay, 6:BookingCheckOut}
     */
    private function createActiveBooking(): array
    {
        $guest = User::factory()->create();
        $host = User::factory()->host()->create();
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'city' => 'Vilnius',
            ]);
        $room = Room::factory()->for($property)->create();
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'display_name' => 'Complaint lower bed',
                'place_number' => 'CMP1',
                'base_price_per_night' => 50,
                'base_price' => 50,
            ]);

        $booking = Booking::factory()->create([
            'bed_id' => null,
            'guest_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'host_id' => $host->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => BookingStatus::InProgress,
            'payment_status' => PaymentStatus::Paid,
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-14',
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-14',
            'check_in_time' => '18:00',
            'arrival_time' => '18:00',
            'nights_count' => 4,
            'nights' => 4,
            'chargeable_days_count' => 4,
            'calendar_presence_days_count' => 5,
            'subtotal' => 200,
            'subtotal_amount' => 200,
            'accommodation_amount' => 200,
            'discount_amount' => 0,
            'cleaning_fee' => 10,
            'cleaning_fee_amount' => 10,
            'deposit' => 50,
            'deposit_amount' => 50,
            'service_fee' => 20,
            'service_fee_amount' => 20,
            'total' => 280,
            'total_amount' => 280,
            'host_payout_amount' => 210,
            'currency' => 'EUR',
            'cancellation_policy' => CancellationPolicy::Flexible,
        ]);

        $checkIn = BookingCheckIn::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'check_in_date' => '2026-07-10',
            'status' => 'completed',
        ]);

        $stay = BookingStay::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => 'active',
            'check_in_date' => '2026-07-10',
            'planned_check_out_date' => '2026-07-14',
            'nights_count' => 4,
            'calendar_presence_days_count' => 5,
        ]);

        $checkOut = BookingCheckOut::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'check_out_date' => '2026-07-14',
        ]);

        return [$guest, $host, $place->fresh(['room', 'property']), $booking, $checkIn, $stay, $checkOut];
    }
}
