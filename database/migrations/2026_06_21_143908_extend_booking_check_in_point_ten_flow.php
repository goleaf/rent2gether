<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_check_ins', function (Blueprint $table): void {
            if (! Schema::hasColumn('booking_check_ins', 'check_in_window')) {
                $table->string('check_in_window')->nullable();
            }

            if (! Schema::hasColumn('booking_check_ins', 'guest_on_the_way_at')) {
                $table->timestamp('guest_on_the_way_at')->nullable();
            }

            if (! Schema::hasColumn('booking_check_ins', 'guest_arrived_at')) {
                $table->timestamp('guest_arrived_at')->nullable();
            }

            if (! Schema::hasColumn('booking_check_ins', 'host_notified_guest_arrived_at')) {
                $table->timestamp('host_notified_guest_arrived_at')->nullable();
            }

            if (! Schema::hasColumn('booking_check_ins', 'instructions_available_at')) {
                $table->timestamp('instructions_available_at')->nullable();
            }

            if (! Schema::hasColumn('booking_check_ins', 'instructions_shown_at')) {
                $table->timestamp('instructions_shown_at')->nullable();
            }

            if (! Schema::hasColumn('booking_check_ins', 'address_shown_at')) {
                $table->timestamp('address_shown_at')->nullable();
            }

            if (! Schema::hasColumn('booking_check_ins', 'access_details_shown_at')) {
                $table->timestamp('access_details_shown_at')->nullable();
            }

            if (! Schema::hasColumn('booking_check_ins', 'host_contact_shown_at')) {
                $table->timestamp('host_contact_shown_at')->nullable();
            }

            if (! Schema::hasColumn('booking_check_ins', 'representative_contact_shown_at')) {
                $table->timestamp('representative_contact_shown_at')->nullable();
            }

            if (! Schema::hasColumn('booking_check_ins', 'door_code_provided')) {
                $table->boolean('door_code_provided')->default(false);
            }

            if (! Schema::hasColumn('booking_check_ins', 'intercom_code_provided')) {
                $table->boolean('intercom_code_provided')->default(false);
            }

            if (! Schema::hasColumn('booking_check_ins', 'key_safe_code_provided')) {
                $table->boolean('key_safe_code_provided')->default(false);
            }

            if (! Schema::hasColumn('booking_check_ins', 'bedding_issued')) {
                $table->boolean('bedding_issued')->default(false);
            }

            if (! Schema::hasColumn('booking_check_ins', 'towel_issued')) {
                $table->boolean('towel_issued')->default(false);
            }

            if (! Schema::hasColumn('booking_check_ins', 'locker_assigned')) {
                $table->boolean('locker_assigned')->default(false);
            }

            if (! Schema::hasColumn('booking_check_ins', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable();
            }

            if (! Schema::hasColumn('booking_check_ins', 'problem_reported_at')) {
                $table->timestamp('problem_reported_at')->nullable();
            }

            if (! Schema::hasColumn('booking_check_ins', 'problem_summary')) {
                $table->text('problem_summary')->nullable();
            }

            if (! Schema::hasColumn('booking_check_ins', 'closed_at')) {
                $table->timestamp('closed_at')->nullable();
            }

            $table->index(['property_id', 'status'], 'booking_check_ins_property_status_idx');
            $table->index(['room_id', 'status'], 'booking_check_ins_room_status_idx');
            $table->index(['sleeping_place_id', 'status'], 'booking_check_ins_place_status_idx');
            $table->index('check_in_date', 'booking_check_ins_check_in_date_idx');
            $table->index('guest_arrived_at', 'booking_check_ins_guest_arrived_at_idx');
            $table->index('checked_in_at', 'booking_check_ins_checked_in_at_idx');
        });

        Schema::create('booking_check_in_instructions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_in_id')->unique()->constrained('booking_check_ins')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('instruction_title')->nullable();
            $table->text('public_instruction_text')->nullable();
            $table->text('address_instruction_text')->nullable();
            $table->text('building_entry_instruction')->nullable();
            $table->text('room_finding_instruction')->nullable();
            $table->text('sleeping_place_instruction')->nullable();
            $table->text('key_pickup_instruction')->nullable();
            $table->text('key_return_instruction')->nullable();
            $table->text('night_entry_instruction')->nullable();
            $table->text('emergency_instruction')->nullable();
            $table->text('exact_address_snapshot')->nullable();
            $table->string('room_identifier_snapshot')->nullable();
            $table->string('sleeping_place_identifier_snapshot')->nullable();
            $table->text('door_code_encrypted')->nullable();
            $table->text('intercom_code_encrypted')->nullable();
            $table->text('key_safe_code_encrypted')->nullable();
            $table->timestamp('visible_from')->nullable();
            $table->timestamp('visible_until')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('property_id');
            $table->index('room_id');
            $table->index('sleeping_place_id');
            $table->index('visible_from');
            $table->index('visible_until');
        });

        Schema::create('booking_check_in_access_disclosures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_in_id')->constrained('booking_check_ins')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('disclosure_type');
            $table->timestamp('shown_at');
            $table->foreignId('shown_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('booking_check_in_id');
            $table->index('booking_id');
            $table->index('guest_user_id');
            $table->index('shown_by_user_id');
            $table->index('disclosure_type');
            $table->index('shown_at');
        });

        Schema::create('booking_check_in_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_in_id')->constrained('booking_check_ins')->cascadeOnDelete();
            $table->string('step_key');
            $table->string('status')->default('pending');
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['booking_check_in_id', 'step_key'], 'booking_check_in_steps_check_in_step_unique');
            $table->index(['booking_check_in_id', 'status']);
            $table->index('completed_by_user_id');
            $table->index('required');
            $table->index('sort_order');
        });

        Schema::create('booking_check_in_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_in_id')->constrained('booking_check_ins')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('media_type');
            $table->string('media_role');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->text('caption')->nullable();
            $table->string('visibility')->default('guest_and_host');
            $table->timestamps();

            $table->index('booking_check_in_id');
            $table->index('booking_id');
            $table->index('uploaded_by_user_id');
            $table->index('media_role');
            $table->index('visibility');
        });

        Schema::create('booking_check_in_problems', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_in_id')->constrained('booking_check_ins')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('problem_type');
            $table->string('severity')->default('medium');
            $table->string('status')->default('reported');
            $table->text('description')->nullable();
            $table->boolean('guest_wants_help')->default(true);
            $table->boolean('guest_wants_relocation')->default(false);
            $table->boolean('guest_wants_cancellation')->default(false);
            $table->boolean('guest_wants_refund')->default(false);
            $table->text('host_response')->nullable();
            $table->unsignedBigInteger('source_created_host_unresponsive_case_id')->nullable();
            $table->unsignedBigInteger('source_created_complaint_case_id')->nullable();
            $table->unsignedBigInteger('source_created_mismatch_report_id')->nullable();
            $table->unsignedBigInteger('source_created_maintenance_request_id')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('booking_check_in_id');
            $table->index('booking_id');
            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index('property_id');
            $table->index('room_id');
            $table->index('sleeping_place_id');
            $table->index('problem_type');
            $table->index('severity');
            $table->index('status');
        });

        Schema::create('booking_check_in_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_in_id')->constrained('booking_check_ins')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_check_in_id');
            $table->index('booking_id');
            $table->index('new_status');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_check_in_status_logs');
        Schema::dropIfExists('booking_check_in_problems');
        Schema::dropIfExists('booking_check_in_media');
        Schema::dropIfExists('booking_check_in_steps');
        Schema::dropIfExists('booking_check_in_access_disclosures');
        Schema::dropIfExists('booking_check_in_instructions');

        Schema::table('booking_check_ins', function (Blueprint $table): void {
            $table->dropIndex('booking_check_ins_property_status_idx');
            $table->dropIndex('booking_check_ins_room_status_idx');
            $table->dropIndex('booking_check_ins_place_status_idx');
            $table->dropIndex('booking_check_ins_check_in_date_idx');
            $table->dropIndex('booking_check_ins_guest_arrived_at_idx');
            $table->dropIndex('booking_check_ins_checked_in_at_idx');

            $table->dropColumn([
                'check_in_window',
                'guest_on_the_way_at',
                'guest_arrived_at',
                'host_notified_guest_arrived_at',
                'instructions_available_at',
                'instructions_shown_at',
                'address_shown_at',
                'access_details_shown_at',
                'host_contact_shown_at',
                'representative_contact_shown_at',
                'door_code_provided',
                'intercom_code_provided',
                'key_safe_code_provided',
                'bedding_issued',
                'towel_issued',
                'locker_assigned',
                'checked_in_at',
                'problem_reported_at',
                'problem_summary',
                'closed_at',
            ]);
        });
    }
};
