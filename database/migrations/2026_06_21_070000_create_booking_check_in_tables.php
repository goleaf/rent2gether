<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_check_ins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->date('check_in_date');
            $table->string('planned_check_in_time')->nullable();
            $table->string('planned_check_in_window')->nullable();
            $table->timestamp('actual_arrival_at')->nullable();
            $table->timestamp('actual_check_in_at')->nullable();
            $table->string('check_in_method')->nullable();
            $table->string('met_by_type')->nullable();
            $table->string('met_by_name')->nullable();
            $table->boolean('keys_handed_over')->default(false);
            $table->unsignedTinyInteger('keys_count')->nullable();
            $table->boolean('door_code_shared')->default(false);
            $table->boolean('intercom_code_shared')->default(false);
            $table->boolean('key_safe_code_shared')->default(false);
            $table->boolean('room_shown')->default(false);
            $table->boolean('sleeping_place_shown')->default(false);
            $table->boolean('rules_explained')->default(false);
            $table->boolean('kitchen_rules_explained')->default(false);
            $table->boolean('bathroom_rules_explained')->default(false);
            $table->boolean('quiet_rules_explained')->default(false);
            $table->boolean('bedding_given')->default(false);
            $table->boolean('towel_given')->default(false);
            $table->boolean('locker_given')->default(false);
            $table->boolean('locker_key_given')->default(false);
            $table->string('before_place_photo_path')->nullable();
            $table->string('before_room_photo_path')->nullable();
            $table->timestamp('guest_confirmed_at')->nullable();
            $table->timestamp('host_confirmed_at')->nullable();
            $table->boolean('has_problem')->default(false);
            $table->string('problem_status')->nullable();
            $table->string('status')->default('not_started');
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->timestamps();

            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index(['property_id', 'check_in_date']);
            $table->index(['room_id', 'check_in_date']);
            $table->index(['sleeping_place_id', 'check_in_date']);
            $table->index(['check_in_date', 'status']);
            $table->index('has_problem');
            $table->index('status');
        });

        Schema::create('booking_check_in_checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_in_id')->constrained('booking_check_ins')->cascadeOnDelete();
            $table->string('item_key');
            $table->string('label_key');
            $table->string('status')->default('pending');
            $table->boolean('required')->default(false);
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['booking_check_in_id', 'status']);
            $table->index(['booking_check_in_id', 'item_key']);
            $table->index('completed_by_user_id');
            $table->index('required');
        });

        Schema::create('booking_check_in_problem_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_in_id')->constrained('booking_check_ins')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('problem_type');
            $table->string('severity')->default('medium');
            $table->text('description')->nullable();
            $table->json('photo_paths_json')->nullable();
            $table->string('status')->default('open');
            $table->text('host_response')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['booking_check_in_id', 'status']);
            $table->index('booking_id');
            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index('problem_type');
            $table->index('severity');
        });

        Schema::create('booking_check_in_alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_check_in_id')->constrained('booking_check_ins')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('alert_type');
            $table->string('severity')->default('medium');
            $table->string('status')->default('open');
            $table->string('message_key');
            $table->json('message_params_json')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['booking_check_in_id', 'status']);
            $table->index('booking_id');
            $table->index(['guest_user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index('alert_type');
            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_check_in_alerts');
        Schema::dropIfExists('booking_check_in_problem_reports');
        Schema::dropIfExists('booking_check_in_checklist_items');
        Schema::dropIfExists('booking_check_ins');
    }
};
