<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $this->addBookingColumns($table);
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $this->addBookingIndexes($table);
        });

        Schema::table('booking_guests', function (Blueprint $table): void {
            if (! Schema::hasColumn('booking_guests', 'guest_name')) {
                $table->string('guest_name')->nullable();
                $table->string('guest_email')->nullable();
                $table->string('guest_phone')->nullable();
                $table->string('guest_type')->default('main_guest');
                $table->string('verification_status')->default('not_required');
                $table->boolean('is_main_guest')->default(false);

                $table->index('guest_type', 'booking_guests_guest_type_index');
                $table->index('verification_status', 'booking_guests_verification_status_index');
            }
        });

        if (! Schema::hasTable('booking_group_links')) {
            Schema::create('booking_group_links', function (Blueprint $table): void {
                $table->id();
                $table->string('group_booking_number');
                $table->foreignId('main_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
                $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
                $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status')->default('active');
                $table->timestamps();

                $table->index('group_booking_number', 'booking_group_links_group_number_index');
                $table->index('main_booking_id', 'booking_group_links_main_booking_id_index');
                $table->index('booking_id', 'booking_group_links_booking_id_index');
                $table->index('guest_user_id', 'booking_group_links_guest_user_id_index');
                $table->index('host_user_id', 'booking_group_links_host_user_id_index');
                $table->index('status', 'booking_group_links_status_index');
            });
        }

        if (! Schema::hasTable('booking_requirements')) {
            Schema::create('booking_requirements', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                $table->string('requirement_key');
                $table->string('status')->default('pending');
                $table->boolean('required')->default(true);
                $table->timestamp('completed_at')->nullable();
                $table->string('message_key')->nullable();
                $table->timestamps();

                $table->index(['booking_id', 'requirement_key'], 'booking_requirements_booking_key_index');
                $table->index(['booking_id', 'status'], 'booking_requirements_booking_status_index');
                $table->index('required', 'booking_requirements_required_index');
            });
        }

        if (! Schema::hasTable('booking_host_responses')) {
            Schema::create('booking_host_responses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('response_type');
                $table->text('message')->nullable();
                $table->string('proposed_check_in_time')->nullable();
                $table->string('proposed_check_out_time')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->index('booking_id', 'booking_host_responses_booking_id_index');
                $table->index('host_user_id', 'booking_host_responses_host_user_id_index');
                $table->index('response_type', 'booking_host_responses_response_type_index');
            });
        }

        if (! Schema::hasTable('booking_status_logs')) {
            Schema::create('booking_status_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('old_status')->nullable();
                $table->string('new_status');
                $table->string('reason_key')->nullable();
                $table->text('note')->nullable();
                $table->json('context_json')->nullable();
                $table->timestamps();

                $table->index('booking_id', 'booking_status_logs_booking_id_index');
                $table->index('new_status', 'booking_status_logs_new_status_index');
                $table->index('user_id', 'booking_status_logs_user_id_index');
                $table->index('created_at', 'booking_status_logs_created_at_index');
            });
        }

        if (! Schema::hasTable('booking_lifecycle_events')) {
            Schema::create('booking_lifecycle_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                $table->string('event_key');
                $table->string('event_type')->default('system');
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamp('occurred_at');
                $table->json('context_json')->nullable();
                $table->timestamps();

                $table->index(['booking_id', 'event_key'], 'booking_lifecycle_events_booking_event_index');
                $table->index('event_type', 'booking_lifecycle_events_event_type_index');
                $table->index(['source_type', 'source_id'], 'booking_lifecycle_events_source_index');
                $table->index('occurred_at', 'booking_lifecycle_events_occurred_at_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_lifecycle_events');
        Schema::dropIfExists('booking_status_logs');
        Schema::dropIfExists('booking_host_responses');
        Schema::dropIfExists('booking_requirements');
        Schema::dropIfExists('booking_group_links');

        Schema::table('booking_guests', function (Blueprint $table): void {
            foreach (['guest_type', 'verification_status'] as $column) {
                if (Schema::hasColumn('booking_guests', $column)) {
                    $table->dropIndex("booking_guests_{$column}_index");
                }
            }

            foreach (['guest_name', 'guest_email', 'guest_phone', 'guest_type', 'verification_status', 'is_main_guest'] as $column) {
                if (Schema::hasColumn('booking_guests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $this->dropBookingIndexes($table);

            foreach ($this->bookingColumns() as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function addBookingColumns(Blueprint $table): void
    {
        if (! Schema::hasColumn('bookings', 'booking_number')) {
            $table->string('booking_number')->nullable()->unique();
        }

        if (! Schema::hasColumn('bookings', 'booking_quote_id')) {
            $table->foreignId('booking_quote_id')->nullable()->constrained('booking_quotes')->nullOnDelete();
        }

        if (! Schema::hasColumn('bookings', 'booking_request_id')) {
            $table->foreignId('booking_request_id')->nullable()->constrained('booking_requests')->nullOnDelete();
        }

        if (! Schema::hasColumn('bookings', 'group_booking_id')) {
            $table->unsignedBigInteger('group_booking_id')->nullable();
        }

        foreach (['parent_booking_id', 'extension_from_booking_id', 'relocation_from_booking_id'] as $column) {
            if (! Schema::hasColumn('bookings', $column)) {
                $table->foreignId($column)->nullable()->constrained('bookings')->nullOnDelete();
            }
        }

        foreach ([
            'approval_type' => 'instant_confirmed',
            'payment_type' => 'full_payment',
            'deposit_mode' => 'without_deposit',
            'guest_group_type' => 'single_guest',
            'verification_status' => 'not_required',
        ] as $column => $default) {
            if (! Schema::hasColumn('bookings', $column)) {
                $table->string($column)->default($default);
            }
        }

        if (! Schema::hasColumn('bookings', 'source_type')) {
            $table->string('source_type')->nullable();
        }

        foreach ([
            'chargeable_days_count',
            'calendar_presence_days_count',
        ] as $column) {
            if (! Schema::hasColumn('bookings', $column)) {
                $table->unsignedSmallInteger($column)->default(0);
            }
        }

        foreach ([
            'included_guests_count' => 1,
            'extra_guests_count' => 0,
        ] as $column => $default) {
            if (! Schema::hasColumn('bookings', $column)) {
                $table->unsignedTinyInteger($column)->default($default);
            }
        }

        if (! Schema::hasColumn('bookings', 'nightly_price_snapshot')) {
            $table->json('nightly_price_snapshot')->nullable();
        }

        foreach ([
            'accommodation_amount',
            'total_without_deposit',
            'total_payable',
            'host_payout_amount',
        ] as $column) {
            if (! Schema::hasColumn('bookings', $column)) {
                $table->decimal($column, 10, 2)->default(0);
            }
        }

        if (! Schema::hasColumn('bookings', 'paid_at')) {
            $table->timestamp('paid_at')->nullable();
        }

        foreach ([
            'requires_phone_verification',
            'requires_identity_verification',
            'requires_document_verification',
            'check_in_instruction_available',
            'has_open_maintenance',
            'has_deposit_issue',
        ] as $column) {
            if (! Schema::hasColumn('bookings', $column)) {
                $table->boolean($column)->default(false);
            }
        }

        foreach ([
            'phone_verified_at',
            'identity_verified_at',
            'documents_verified_at',
            'rejected_at',
            'guest_check_in_confirmed_at',
            'host_check_in_confirmed_at',
            'guest_check_out_confirmed_at',
            'host_check_out_confirmed_at',
            'stay_started_at',
            'stay_ended_at',
            'guest_review_left_at',
            'host_review_left_at',
            'closed_at',
        ] as $column) {
            if (! Schema::hasColumn('bookings', $column)) {
                $table->timestamp($column)->nullable();
            }
        }

        if (! Schema::hasColumn('bookings', 'rejection_reason')) {
            $table->text('rejection_reason')->nullable();
        }

        if (! Schema::hasColumn('bookings', 'rejected_by_user_id')) {
            $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        }

        if (! Schema::hasColumn('bookings', 'cancelled_by_user_id')) {
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        }

        if (! Schema::hasColumn('bookings', 'cancelled_by_type')) {
            $table->string('cancelled_by_type')->nullable();
        }

        if (! Schema::hasColumn('bookings', 'cancellation_policy_snapshot_id')) {
            $table->unsignedBigInteger('cancellation_policy_snapshot_id')->nullable();
        }
    }

    private function addBookingIndexes(Blueprint $table): void
    {
        foreach ([
            'booking_quote_id',
            'booking_request_id',
            'group_booking_id',
            'parent_booking_id',
            'extension_from_booking_id',
            'relocation_from_booking_id',
            'payment_status',
            'verification_status',
            'booking_type',
            'status',
            'has_dispute',
            'has_complaint',
            'created_at',
            'cancellation_policy_snapshot_id',
        ] as $column) {
            if (Schema::hasColumn('bookings', $column)) {
                $table->index($column, "bookings_core_{$column}_index");
            }
        }

        if (Schema::hasColumn('bookings', 'property_id')) {
            $table->index(['property_id', 'status'], 'bookings_core_property_status_index');
        }

        if (Schema::hasColumn('bookings', 'room_id')) {
            $table->index(['room_id', 'status'], 'bookings_core_room_status_index');
        }

        if (Schema::hasColumn('bookings', 'sleeping_place_id')) {
            $table->index(['sleeping_place_id', 'status'], 'bookings_core_place_status_index');
        }

        if (Schema::hasColumn('bookings', 'check_in_date') && Schema::hasColumn('bookings', 'check_out_date')) {
            $table->index(['check_in_date', 'check_out_date'], 'bookings_core_checkin_checkout_index');
        }
    }

    private function dropBookingIndexes(Blueprint $table): void
    {
        foreach ([
            'booking_quote_id',
            'booking_request_id',
            'group_booking_id',
            'parent_booking_id',
            'extension_from_booking_id',
            'relocation_from_booking_id',
            'payment_status',
            'verification_status',
            'booking_type',
            'status',
            'has_dispute',
            'has_complaint',
            'created_at',
            'cancellation_policy_snapshot_id',
        ] as $column) {
            if (Schema::hasColumn('bookings', $column)) {
                $table->dropIndex("bookings_core_{$column}_index");
            }
        }

        foreach ([
            'bookings_core_property_status_index',
            'bookings_core_room_status_index',
            'bookings_core_place_status_index',
            'bookings_core_checkin_checkout_index',
        ] as $index) {
            $table->dropIndex($index);
        }
    }

    /**
     * @return list<string>
     */
    private function bookingColumns(): array
    {
        return [
            'booking_number',
            'booking_quote_id',
            'booking_request_id',
            'group_booking_id',
            'parent_booking_id',
            'extension_from_booking_id',
            'relocation_from_booking_id',
            'approval_type',
            'payment_type',
            'deposit_mode',
            'guest_group_type',
            'source_type',
            'chargeable_days_count',
            'calendar_presence_days_count',
            'included_guests_count',
            'extra_guests_count',
            'nightly_price_snapshot',
            'accommodation_amount',
            'total_without_deposit',
            'total_payable',
            'host_payout_amount',
            'paid_at',
            'requires_phone_verification',
            'requires_identity_verification',
            'requires_document_verification',
            'verification_status',
            'phone_verified_at',
            'identity_verified_at',
            'documents_verified_at',
            'rejection_reason',
            'rejected_by_user_id',
            'rejected_at',
            'cancelled_by_user_id',
            'cancelled_by_type',
            'cancellation_policy_snapshot_id',
            'check_in_instruction_available',
            'guest_check_in_confirmed_at',
            'host_check_in_confirmed_at',
            'guest_check_out_confirmed_at',
            'host_check_out_confirmed_at',
            'stay_started_at',
            'stay_ended_at',
            'has_open_maintenance',
            'has_deposit_issue',
            'guest_review_left_at',
            'host_review_left_at',
            'closed_at',
        ];
    }
};
