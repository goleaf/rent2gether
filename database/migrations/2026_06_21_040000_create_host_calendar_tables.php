<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('host_calendar_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cleaning_task_id')->nullable()->constrained('host_cleaning_tasks')->nullOnDelete();
            $table->string('event_type');
            $table->string('event_status')->nullable();
            $table->date('event_date');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('title_key');
            $table->text('title_params_json')->nullable();
            $table->string('description_key')->nullable();
            $table->text('description_params_json')->nullable();
            $table->foreignId('guest_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_display_name')->nullable();
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->unsignedSmallInteger('nights_count')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('check_in_status')->nullable();
            $table->string('place_status')->nullable();
            $table->boolean('needs_cleaning')->default(false);
            $table->boolean('needs_inspection')->default(false);
            $table->boolean('needs_repair')->default(false);
            $table->decimal('price_amount', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('payout_status')->nullable();
            $table->decimal('payout_amount', 10, 2)->nullable();
            $table->integer('priority')->default(0);
            $table->string('source')->nullable();
            $table->text('host_note')->nullable();
            $table->boolean('is_private')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'event_date'], 'host_calendar_events_user_date_index');
            $table->index(['user_id', 'event_type', 'event_date'], 'host_calendar_events_user_type_date_index');
            $table->index(['property_id', 'event_date'], 'host_calendar_events_property_date_index');
            $table->index(['room_id', 'event_date'], 'host_calendar_events_room_date_index');
            $table->index(['sleeping_place_id', 'event_date'], 'host_calendar_events_place_date_index');
            $table->index('booking_id', 'host_calendar_events_booking_index');
            $table->index('cleaning_task_id', 'host_calendar_events_cleaning_task_index');
            $table->index('guest_user_id', 'host_calendar_events_guest_user_index');
            $table->index('event_status', 'host_calendar_events_status_index');
            $table->index('needs_cleaning', 'host_calendar_events_needs_cleaning_index');
            $table->index('needs_inspection', 'host_calendar_events_needs_inspection_index');
            $table->index('needs_repair', 'host_calendar_events_needs_repair_index');
            $table->index('payout_status', 'host_calendar_events_payout_status_index');
        });

        Schema::create('host_calendar_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->date('note_date');
            $table->string('note_type')->default('general');
            $table->text('note');
            $table->boolean('is_private')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'note_date'], 'host_calendar_notes_user_date_index');
            $table->index(['property_id', 'note_date'], 'host_calendar_notes_property_date_index');
            $table->index(['room_id', 'note_date'], 'host_calendar_notes_room_date_index');
            $table->index(['sleeping_place_id', 'note_date'], 'host_calendar_notes_place_date_index');
            $table->index('booking_id', 'host_calendar_notes_booking_index');
        });

        Schema::create('host_calendar_view_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('default_view')->default('property');
            $table->foreignId('default_property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->foreignId('default_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->boolean('show_prices')->default(true);
            $table->boolean('show_guest_names')->default(true);
            $table->boolean('show_cleaning')->default(true);
            $table->boolean('show_repairs')->default(true);
            $table->boolean('show_payouts')->default(true);
            $table->boolean('show_occupancy')->default(true);
            $table->boolean('compact_mode')->default(true);
            $table->timestamps();

            $table->index('default_property_id', 'host_calendar_view_settings_default_property_index');
            $table->index('default_room_id', 'host_calendar_view_settings_default_room_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('host_calendar_view_settings');
        Schema::dropIfExists('host_calendar_notes');
        Schema::dropIfExists('host_calendar_events');
    }
};
