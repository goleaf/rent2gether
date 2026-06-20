<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sleeping_place_calendar_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('default_status')->default('available')->index();
            $table->decimal('default_price', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->unsignedSmallInteger('min_nights')->nullable()->index();
            $table->unsignedSmallInteger('max_nights')->nullable()->index();
            $table->decimal('weekly_discount_percent', 5, 2)->nullable();
            $table->decimal('monthly_discount_percent', 5, 2)->nullable();
            $table->unsignedSmallInteger('cleaning_gap_hours')->default(0);
            $table->unsignedTinyInteger('cleaning_gap_days')->default(0);
            $table->boolean('instant_booking_enabled')->default(false)->index();
            $table->boolean('requires_host_approval')->default(true)->index();
            $table->boolean('can_extend')->default(true);
            $table->boolean('same_day_check_in_allowed')->default(true);
            $table->boolean('same_day_turnover_allowed')->default(false);
            $table->string('check_in_time_from')->nullable();
            $table->string('check_in_time_until')->nullable();
            $table->string('check_out_time_until')->nullable();
            $table->timestamps();
        });

        Schema::create('sleeping_place_calendar_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('available');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->unsignedSmallInteger('min_nights')->nullable();
            $table->unsignedSmallInteger('max_nights')->nullable();
            $table->boolean('check_in_allowed')->default(true);
            $table->boolean('check_out_allowed')->default(true);
            $table->string('reason')->nullable();
            $table->string('source')->nullable();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('blocked_by_host')->default(false);
            $table->timestamps();

            $table->unique(['sleeping_place_id', 'date'], 'sp_calendar_days_place_date_unique');
            $table->index(['sleeping_place_id', 'status'], 'sp_calendar_days_place_status_index');
            $table->index(['date', 'status'], 'sp_calendar_days_date_status_index');
            $table->index('booking_id', 'sp_calendar_days_booking_id_index');
            $table->index('check_in_allowed', 'sp_calendar_days_check_in_index');
            $table->index('check_out_allowed', 'sp_calendar_days_check_out_index');
            $table->index('blocked_by_host', 'sp_calendar_days_blocked_by_host_index');
        });

        Schema::create('sleeping_place_calendar_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('rule_type');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->json('weekdays_json')->nullable();
            $table->string('status')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedSmallInteger('min_nights')->nullable();
            $table->unsignedSmallInteger('max_nights')->nullable();
            $table->boolean('check_in_allowed')->nullable();
            $table->boolean('check_out_allowed')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();

            $table->index(['sleeping_place_id', 'rule_type'], 'sp_calendar_rules_place_type_index');
            $table->index(['starts_at', 'ends_at'], 'sp_calendar_rules_range_index');
            $table->index('priority', 'sp_calendar_rules_priority_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sleeping_place_calendar_rules');
        Schema::dropIfExists('sleeping_place_calendar_days');
        Schema::dropIfExists('sleeping_place_calendar_settings');
    }
};
