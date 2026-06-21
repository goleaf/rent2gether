<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->expandCalendarSettings();
        $this->expandCalendarDays();
        $this->createTurnoverRules();
        $this->createCalendarBlocks();
        $this->createBookingDateLocks();
        $this->createAvailabilityStatusLogs();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sleeping_place_booking_date_locks')) {
            DB::statement('DROP INDEX IF EXISTS sleeping_place_active_date_lock_unique');
        }

        Schema::dropIfExists('sleeping_place_availability_status_logs');
        Schema::dropIfExists('sleeping_place_booking_date_locks');
        Schema::dropIfExists('sleeping_place_calendar_blocks');
        Schema::dropIfExists('sleeping_place_turnover_rules');

        if (Schema::hasTable('sleeping_place_calendar_days')) {
            Schema::table('sleeping_place_calendar_days', function (Blueprint $table): void {
                foreach ([
                    'sp_calendar_days_date_index',
                    'sp_calendar_days_status_index',
                    'sp_calendar_days_price_override_index',
                    'sp_calendar_days_source_index',
                ] as $index) {
                    if (Schema::hasIndex('sleeping_place_calendar_days', $index)) {
                        $table->dropIndex($index);
                    }
                }

                foreach ([
                    'price_override',
                    'reason_key',
                    'source_type',
                    'source_id',
                    'note',
                ] as $column) {
                    if (Schema::hasColumn('sleeping_place_calendar_days', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('sleeping_place_calendar_settings')) {
            Schema::table('sleeping_place_calendar_settings', function (Blueprint $table): void {
                foreach ([
                    'sp_calendar_settings_booking_mode_index',
                    'sp_calendar_settings_active_index',
                ] as $index) {
                    if (Schema::hasIndex('sleeping_place_calendar_settings', $index)) {
                        $table->dropIndex($index);
                    }
                }

                foreach ([
                    'booking_mode',
                    'requires_payment_before_block',
                    'requires_host_confirmation',
                    'request_only',
                    'default_check_in_time',
                    'default_check_out_time',
                    'earliest_check_in_time',
                    'latest_check_out_time',
                    'check_in_weekdays_json',
                    'check_out_weekdays_json',
                    'active',
                ] as $column) {
                    if (Schema::hasColumn('sleeping_place_calendar_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function expandCalendarSettings(): void
    {
        Schema::table('sleeping_place_calendar_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('sleeping_place_calendar_settings', 'booking_mode')) {
                $table->string('booking_mode')->default('instant');
            }

            if (! Schema::hasColumn('sleeping_place_calendar_settings', 'requires_payment_before_block')) {
                $table->boolean('requires_payment_before_block')->default(true);
            }

            if (! Schema::hasColumn('sleeping_place_calendar_settings', 'requires_host_confirmation')) {
                $table->boolean('requires_host_confirmation')->default(false);
            }

            if (! Schema::hasColumn('sleeping_place_calendar_settings', 'request_only')) {
                $table->boolean('request_only')->default(false);
            }

            if (! Schema::hasColumn('sleeping_place_calendar_settings', 'default_check_in_time')) {
                $table->string('default_check_in_time')->nullable();
            }

            if (! Schema::hasColumn('sleeping_place_calendar_settings', 'default_check_out_time')) {
                $table->string('default_check_out_time')->nullable();
            }

            if (! Schema::hasColumn('sleeping_place_calendar_settings', 'earliest_check_in_time')) {
                $table->string('earliest_check_in_time')->nullable();
            }

            if (! Schema::hasColumn('sleeping_place_calendar_settings', 'latest_check_out_time')) {
                $table->string('latest_check_out_time')->nullable();
            }

            if (! Schema::hasColumn('sleeping_place_calendar_settings', 'check_in_weekdays_json')) {
                $table->json('check_in_weekdays_json')->nullable();
            }

            if (! Schema::hasColumn('sleeping_place_calendar_settings', 'check_out_weekdays_json')) {
                $table->json('check_out_weekdays_json')->nullable();
            }

            if (! Schema::hasColumn('sleeping_place_calendar_settings', 'active')) {
                $table->boolean('active')->default(true);
            }
        });

        Schema::table('sleeping_place_calendar_settings', function (Blueprint $table): void {
            if (! Schema::hasIndex('sleeping_place_calendar_settings', 'sp_calendar_settings_booking_mode_index')) {
                $table->index('booking_mode', 'sp_calendar_settings_booking_mode_index');
            }

            if (! Schema::hasIndex('sleeping_place_calendar_settings', 'sp_calendar_settings_active_index')) {
                $table->index('active', 'sp_calendar_settings_active_index');
            }
        });
    }

    private function expandCalendarDays(): void
    {
        Schema::table('sleeping_place_calendar_days', function (Blueprint $table): void {
            if (! Schema::hasColumn('sleeping_place_calendar_days', 'price_override')) {
                $table->decimal('price_override', 10, 2)->nullable();
            }

            if (! Schema::hasColumn('sleeping_place_calendar_days', 'reason_key')) {
                $table->string('reason_key')->nullable();
            }

            if (! Schema::hasColumn('sleeping_place_calendar_days', 'source_type')) {
                $table->string('source_type')->nullable();
            }

            if (! Schema::hasColumn('sleeping_place_calendar_days', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable();
            }

            if (! Schema::hasColumn('sleeping_place_calendar_days', 'note')) {
                $table->text('note')->nullable();
            }
        });

        Schema::table('sleeping_place_calendar_days', function (Blueprint $table): void {
            if (! Schema::hasIndex('sleeping_place_calendar_days', 'sp_calendar_days_date_index')) {
                $table->index('date', 'sp_calendar_days_date_index');
            }

            if (! Schema::hasIndex('sleeping_place_calendar_days', 'sp_calendar_days_status_index')) {
                $table->index('status', 'sp_calendar_days_status_index');
            }

            if (! Schema::hasIndex('sleeping_place_calendar_days', 'sp_calendar_days_price_override_index')) {
                $table->index('price_override', 'sp_calendar_days_price_override_index');
            }

            if (! Schema::hasIndex('sleeping_place_calendar_days', 'sp_calendar_days_source_index')) {
                $table->index(['source_type', 'source_id'], 'sp_calendar_days_source_index');
            }
        });
    }

    private function createTurnoverRules(): void
    {
        Schema::create('sleeping_place_turnover_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('min_gap_minutes')->default(0);
            $table->boolean('cleaning_required_between_guests')->default(true);
            $table->unsignedInteger('cleaning_gap_minutes')->default(0);
            $table->boolean('inspection_required_after_checkout')->default(false);
            $table->unsignedInteger('inspection_gap_minutes')->default(0);
            $table->boolean('same_day_turnover_allowed')->default(false);
            $table->boolean('same_day_turnover_requires_cleaning_done')->default(true);
            $table->boolean('same_day_turnover_requires_inspection_done')->default(false);
            $table->string('earliest_new_check_in_time')->nullable();
            $table->string('latest_previous_check_out_time')->nullable();
            $table->timestamps();

            $table->index('min_gap_minutes', 'sp_turnover_min_gap_index');
            $table->index('same_day_turnover_allowed', 'sp_turnover_same_day_index');
            $table->index('cleaning_required_between_guests', 'sp_turnover_cleaning_index');
        });
    }

    private function createCalendarBlocks(): void
    {
        Schema::create('sleeping_place_calendar_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('block_type');
            $table->string('status')->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->string('reason_key')->nullable();
            $table->boolean('visible_to_guest')->default(false);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['sleeping_place_id', 'status'], 'sp_calendar_blocks_place_status_index');
            $table->index(['sleeping_place_id', 'starts_at'], 'sp_calendar_blocks_place_starts_index');
            $table->index(['sleeping_place_id', 'ends_at'], 'sp_calendar_blocks_place_ends_index');
            $table->index(['room_id', 'status'], 'sp_calendar_blocks_room_status_index');
            $table->index(['property_id', 'status'], 'sp_calendar_blocks_property_status_index');
            $table->index('booking_id', 'sp_calendar_blocks_booking_index');
            $table->index(['source_type', 'source_id'], 'sp_calendar_blocks_source_index');
            $table->index('block_type', 'sp_calendar_blocks_type_index');
            $table->index('status', 'sp_calendar_blocks_status_index');
            $table->index('created_by_user_id', 'sp_calendar_blocks_created_by_index');
        });
    }

    private function createBookingDateLocks(): void
    {
        Schema::create('sleeping_place_booking_date_locks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('booking_quote_id')->nullable();
            $table->unsignedBigInteger('booking_request_id')->nullable();
            $table->foreignId('booking_extension_id')->nullable()->constrained('booking_extensions')->nullOnDelete();
            $table->unsignedBigInteger('booking_relocation_id')->nullable();
            $table->date('date');
            $table->string('lock_type');
            $table->string('status')->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['sleeping_place_id', 'date'], 'sp_date_locks_place_date_index');
            $table->index('booking_id', 'sp_date_locks_booking_index');
            $table->index('booking_quote_id', 'sp_date_locks_quote_index');
            $table->index('booking_request_id', 'sp_date_locks_request_index');
            $table->index('booking_extension_id', 'sp_date_locks_extension_index');
            $table->index('booking_relocation_id', 'sp_date_locks_relocation_index');
            $table->index('expires_at', 'sp_date_locks_expires_index');
            $table->index('status', 'sp_date_locks_status_index');
            $table->index('lock_type', 'sp_date_locks_type_index');
        });

        DB::statement(
            "CREATE UNIQUE INDEX sleeping_place_active_date_lock_unique ON sleeping_place_booking_date_locks (sleeping_place_id, date) WHERE status = 'active'"
        );
    }

    private function createAvailabilityStatusLogs(): void
    {
        Schema::create('sleeping_place_availability_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->date('date')->nullable();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['sleeping_place_id', 'date'], 'sp_availability_logs_place_date_index');
            $table->index(['source_type', 'source_id'], 'sp_availability_logs_source_index');
            $table->index('new_status', 'sp_availability_logs_new_status_index');
            $table->index('user_id', 'sp_availability_logs_user_index');
            $table->index('created_at', 'sp_availability_logs_created_index');
        });
    }
};
