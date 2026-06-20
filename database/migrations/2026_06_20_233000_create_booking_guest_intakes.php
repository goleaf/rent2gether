<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_guest_intakes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->string('trip_purpose')->nullable();
            $table->text('trip_purpose_other')->nullable();
            $table->string('trip_purpose_visibility')->default('safe');
            $table->date('planned_arrival_date')->nullable();
            $table->string('planned_arrival_time')->nullable();
            $table->string('planned_arrival_window')->nullable();
            $table->string('planned_departure_time')->nullable();
            $table->boolean('arrival_time_unknown')->default(false);
            $table->boolean('departure_time_unknown')->default(false);
            $table->boolean('early_check_in_requested')->default(false);
            $table->string('requested_early_check_in_time')->nullable();
            $table->boolean('late_check_in_requested')->default(false);
            $table->string('requested_late_check_in_time')->nullable();
            $table->boolean('late_check_out_requested')->default(false);
            $table->string('requested_late_check_out_time')->nullable();
            $table->boolean('early_check_out_requested')->default(false);
            $table->string('requested_early_check_out_time')->nullable();
            $table->boolean('can_adjust_arrival_time')->default(true);
            $table->string('baggage_level')->nullable();
            $table->unsignedTinyInteger('baggage_count')->nullable();
            $table->boolean('has_large_suitcase')->nullable();
            $table->boolean('has_special_baggage')->nullable();
            $table->string('special_baggage_type')->nullable();
            $table->boolean('needs_luggage_storage_before_checkin')->default(false);
            $table->boolean('needs_luggage_storage_after_checkout')->default(false);
            $table->boolean('has_pet')->default(false);
            $table->string('pet_type')->nullable();
            $table->string('pet_size')->nullable();
            $table->text('pet_notes')->nullable();
            $table->boolean('smokes')->nullable();
            $table->string('smoking_type')->nullable();
            $table->boolean('accepts_smoking_rules')->default(false);
            $table->boolean('needs_quiet')->default(false);
            $table->string('noise_sensitivity_level')->nullable();
            $table->boolean('needs_workspace')->default(false);
            $table->boolean('needs_fast_wifi')->default(false);
            $table->boolean('needs_power_socket')->default(false);
            $table->boolean('needs_online_calls')->default(false);
            $table->boolean('needs_late_entry')->default(false);
            $table->boolean('needs_self_check_in')->default(false);
            $table->boolean('needs_registration')->default(false);
            $table->boolean('needs_work_documents')->default(false);
            $table->boolean('needs_invoice')->default(false);
            $table->boolean('needs_receipt')->default(false);
            $table->boolean('needs_contract')->default(false);
            $table->string('company_name')->nullable();
            $table->text('document_notes')->nullable();
            $table->text('special_requests')->nullable();
            $table->text('host_message')->nullable();
            $table->text('auto_generated_host_message')->nullable();
            $table->boolean('rules_accepted')->default(false);
            $table->timestamp('rules_accepted_at')->nullable();
            $table->timestamp('compatibility_checked_at')->nullable();
            $table->string('compatibility_status')->nullable();
            $table->unsignedTinyInteger('compatibility_score')->nullable();
            $table->json('warnings_json')->nullable();
            $table->json('blocking_reasons_json')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('booking_id');
            $table->index('property_id');
            $table->index('room_id');
            $table->index('sleeping_place_id');
            $table->index('trip_purpose');
            $table->index('early_check_in_requested');
            $table->index('late_check_out_requested');
            $table->index('has_pet');
            $table->index('smokes');
            $table->index('needs_quiet');
            $table->index('needs_workspace');
            $table->index('needs_fast_wifi');
            $table->index('needs_registration');
            $table->index('needs_work_documents');
            $table->index('compatibility_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_guest_intakes');
    }
};
