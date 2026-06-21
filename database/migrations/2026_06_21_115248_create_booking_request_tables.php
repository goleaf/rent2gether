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
        Schema::create('booking_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('booking_quote_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('request_type');
            $table->string('status')->default('submitted');
            $table->boolean('hold_dates')->default(false);
            $table->timestamp('hold_expires_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->date('check_in_date');
            $table->string('check_in_time')->nullable();
            $table->date('check_out_date');
            $table->string('check_out_time')->nullable();
            $table->unsignedInteger('nights_count')->default(0);
            $table->unsignedInteger('chargeable_days_count')->default(0);
            $table->unsignedInteger('calendar_presence_days_count')->default(0);
            $table->unsignedTinyInteger('guests_count')->default(1);
            $table->string('trip_purpose')->nullable();
            $table->string('planned_arrival_time')->nullable();
            $table->string('planned_departure_time')->nullable();
            $table->text('guest_message')->nullable();
            $table->boolean('has_baggage')->default(false);
            $table->boolean('needs_luggage_storage')->default(false);
            $table->boolean('needs_early_check_in')->default(false);
            $table->boolean('needs_late_checkout')->default(false);
            $table->boolean('needs_residence_registration')->default(false);
            $table->boolean('needs_reporting_documents')->default(false);
            $table->boolean('guest_agreed_to_rules')->default(false);
            $table->boolean('guest_agreed_to_cancellation_policy')->default(false);
            $table->boolean('guest_agreed_to_deposit_policy')->default(false);
            $table->json('guest_profile_snapshot_json')->nullable();
            $table->json('guest_rating_snapshot_json')->nullable();
            $table->json('compatibility_snapshot_json')->nullable();
            $table->json('price_snapshot_json')->nullable();
            $table->json('warnings_snapshot_json')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('cleaning_fee_amount', 10, 2)->default(0);
            $table->decimal('service_fee_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->text('host_response')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('converted_to_booking_at')->nullable();
            $table->timestamps();

            $table->index('booking_quote_id');
            $table->index('booking_id');
            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index(['property_id', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['sleeping_place_id', 'status']);
            $table->index('check_in_date');
            $table->index('check_out_date');
            $table->index('request_type');
            $table->index('status');
            $table->index('expires_at');
            $table->index('hold_expires_at');
            $table->index('created_at');
        });

        Schema::create('booking_request_warnings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_request_id')->constrained()->cascadeOnDelete();
            $table->string('warning_key');
            $table->string('severity')->default('warning');
            $table->string('message_key');
            $table->json('message_params_json')->nullable();
            $table->boolean('blocking')->default(false);
            $table->boolean('visible_to_host')->default(true);
            $table->boolean('visible_to_guest')->default(false);
            $table->timestamps();

            $table->index(['booking_request_id', 'warning_key'], 'br_warnings_request_key_index');
            $table->index(['booking_request_id', 'severity'], 'br_warnings_request_severity_index');
            $table->index('blocking', 'br_warnings_blocking_index');
            $table->index('visible_to_host', 'br_warnings_host_index');
            $table->index('visible_to_guest', 'br_warnings_guest_index');
        });

        Schema::create('booking_request_host_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('response_type');
            $table->text('message')->nullable();
            $table->string('proposed_check_in_time')->nullable();
            $table->string('proposed_check_out_time')->nullable();
            $table->date('proposed_check_in_date')->nullable();
            $table->date('proposed_check_out_date')->nullable();
            $table->foreignId('alternative_sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->foreignId('alternative_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('booking_request_id', 'br_host_responses_request_index');
            $table->index('host_user_id', 'br_host_responses_host_index');
            $table->index('response_type', 'br_host_responses_type_index');
        });

        Schema::create('booking_request_guest_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('response_type');
            $table->text('message')->nullable();
            $table->string('accepted_proposed_check_in_time')->nullable();
            $table->string('accepted_proposed_check_out_time')->nullable();
            $table->date('accepted_proposed_check_in_date')->nullable();
            $table->date('accepted_proposed_check_out_date')->nullable();
            $table->foreignId('accepted_alternative_sleeping_place_id')->nullable()->constrained('sleeping_places')->nullOnDelete();
            $table->timestamps();

            $table->index('booking_request_id', 'br_guest_responses_request_index');
            $table->index('guest_user_id', 'br_guest_responses_guest_index');
            $table->index('response_type', 'br_guest_responses_type_index');
        });

        Schema::create('booking_request_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_request_id', 'br_status_logs_request_index');
            $table->index('new_status', 'br_status_logs_status_index');
            $table->index('user_id', 'br_status_logs_user_index');
            $table->index('created_at', 'br_status_logs_created_index');
        });

        Schema::create('booking_request_compatibility_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_request_id')->constrained()->cascadeOnDelete();
            $table->string('compatibility_key');
            $table->string('status');
            $table->string('severity')->default('info');
            $table->string('message_key');
            $table->json('message_params_json')->nullable();
            $table->timestamps();

            $table->index(['booking_request_id', 'compatibility_key'], 'br_compat_request_key_index');
            $table->index('status', 'br_compat_status_index');
            $table->index('severity', 'br_compat_severity_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_request_compatibility_results');
        Schema::dropIfExists('booking_request_status_logs');
        Schema::dropIfExists('booking_request_guest_responses');
        Schema::dropIfExists('booking_request_host_responses');
        Schema::dropIfExists('booking_request_warnings');
        Schema::dropIfExists('booking_requests');
    }
};
