<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('host_current_stay_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('guest_display_name')->nullable();
            $table->string('guest_avatar_url')->nullable();
            $table->string('room_label')->nullable();
            $table->string('sleeping_place_label')->nullable();
            $table->date('check_in_date');
            $table->string('check_in_time')->nullable();
            $table->date('check_out_date');
            $table->string('check_out_time')->nullable();
            $table->unsignedSmallInteger('nights_count')->nullable();
            $table->integer('nights_left')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('stay_status')->nullable();
            $table->string('check_in_status')->nullable();
            $table->string('payout_status')->nullable();
            $table->decimal('booking_total_amount', 10, 2)->nullable();
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->decimal('remaining_amount', 10, 2)->nullable();
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->decimal('cleaning_fee_amount', 10, 2)->nullable();
            $table->boolean('has_special_requests')->default(false);
            $table->text('special_requests_summary')->nullable();
            $table->decimal('guest_rating_average', 3, 2)->nullable();
            $table->decimal('roommate_rating_average', 3, 2)->nullable();
            $table->boolean('has_complaints')->default(false);
            $table->unsignedInteger('open_complaints_count')->default(0);
            $table->boolean('needs_extension')->default(false);
            $table->timestamp('extension_requested_at')->nullable();
            $table->boolean('needs_checkout')->default(false);
            $table->boolean('checkout_due_today')->default(false);
            $table->boolean('checkout_overdue')->default(false);
            $table->boolean('needs_cleaning_after_checkout')->default(false);
            $table->boolean('needs_inspection')->default(false);
            $table->boolean('needs_repair')->default(false);
            $table->text('last_host_note')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'stay_status'], 'current_stays_user_stay_index');
            $table->index(['user_id', 'check_out_date'], 'current_stays_user_checkout_index');
            $table->index(['user_id', 'payment_status'], 'current_stays_user_payment_index');
            $table->index(['user_id', 'has_complaints'], 'current_stays_user_complaints_index');
            $table->index(['user_id', 'needs_extension'], 'current_stays_user_extension_index');
            $table->index(['user_id', 'needs_checkout'], 'current_stays_user_checkout_need_index');
            $table->index(['property_id', 'stay_status'], 'current_stays_property_stay_index');
            $table->index(['room_id', 'stay_status'], 'current_stays_room_stay_index');
            $table->index(['sleeping_place_id', 'stay_status'], 'current_stays_place_stay_index');
            $table->index('guest_user_id', 'current_stays_guest_index');
        });

        Schema::create('host_guest_stay_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('note');
            $table->string('importance')->default('normal');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'guest_user_id'], 'guest_stay_notes_user_guest_index');
            $table->index('guest_user_id', 'guest_stay_notes_guest_index');
            $table->index('booking_id', 'guest_stay_notes_booking_index');
            $table->index('property_id', 'guest_stay_notes_property_index');
            $table->index('room_id', 'guest_stay_notes_room_index');
            $table->index('sleeping_place_id', 'guest_stay_notes_place_index');
            $table->index('is_pinned', 'guest_stay_notes_pinned_index');
        });

        Schema::create('host_guest_stay_flags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('flag_key');
            $table->string('status')->default('open');
            $table->string('severity')->default('medium');
            $table->string('message_key');
            $table->json('message_params_json')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'guest_stay_flags_user_status_index');
            $table->index(['booking_id', 'status'], 'guest_stay_flags_booking_status_index');
            $table->index(['guest_user_id', 'status'], 'guest_stay_flags_guest_status_index');
            $table->index('flag_key', 'guest_stay_flags_key_index');
            $table->index('severity', 'guest_stay_flags_severity_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('host_guest_stay_flags');
        Schema::dropIfExists('host_guest_stay_notes');
        Schema::dropIfExists('host_current_stay_snapshots');
    }
};
