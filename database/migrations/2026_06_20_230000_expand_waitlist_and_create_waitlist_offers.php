<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('waitlist_items')) {
            return;
        }

        Schema::table('waitlist_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('waitlist_items', 'property_id')) {
                $table->foreignId('property_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('waitlist_items', 'room_id')) {
                $table->foreignId('room_id')->nullable()->after('property_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('waitlist_items', 'source')) {
                $table->string('source')->nullable()->after('sleeping_place_id');
            }

            if (! Schema::hasColumn('waitlist_items', 'desired_check_in_date')) {
                $table->date('desired_check_in_date')->nullable()->after('desired_check_out');
            }

            if (! Schema::hasColumn('waitlist_items', 'desired_check_out_date')) {
                $table->date('desired_check_out_date')->nullable()->after('desired_check_in_date');
            }

            if (! Schema::hasColumn('waitlist_items', 'nights_count')) {
                $table->unsignedSmallInteger('nights_count')->nullable()->after('desired_check_out_date');
            }

            if (! Schema::hasColumn('waitlist_items', 'calendar_days_count')) {
                $table->unsignedSmallInteger('calendar_days_count')->nullable()->after('nights_count');
            }

            if (! Schema::hasColumn('waitlist_items', 'guests_count')) {
                $table->unsignedTinyInteger('guests_count')->default(1)->after('calendar_days_count');
            }

            if (! Schema::hasColumn('waitlist_items', 'flexible_dates')) {
                $table->boolean('flexible_dates')->default(false)->after('guests_count');
            }

            if (! Schema::hasColumn('waitlist_items', 'flexible_days')) {
                $table->unsignedTinyInteger('flexible_days')->nullable()->after('flexible_dates');
            }

            if (! Schema::hasColumn('waitlist_items', 'min_nights')) {
                $table->unsignedSmallInteger('min_nights')->nullable()->after('flexible_days');
            }

            if (! Schema::hasColumn('waitlist_items', 'max_nights')) {
                $table->unsignedSmallInteger('max_nights')->nullable()->after('min_nights');
            }

            if (! Schema::hasColumn('waitlist_items', 'max_price_per_night')) {
                $table->decimal('max_price_per_night', 10, 2)->nullable()->after('max_price');
            }

            if (! Schema::hasColumn('waitlist_items', 'max_total_price')) {
                $table->decimal('max_total_price', 10, 2)->nullable()->after('max_price_per_night');
            }

            if (! Schema::hasColumn('waitlist_items', 'max_deposit')) {
                $table->decimal('max_deposit', 10, 2)->nullable()->after('max_total_price');
            }

            if (! Schema::hasColumn('waitlist_items', 'currency')) {
                $table->string('currency', 3)->nullable()->after('max_deposit');
            }

            if (! Schema::hasColumn('waitlist_items', 'ready_to_book_immediately')) {
                $table->boolean('ready_to_book_immediately')->default(false)->after('ready_to_book');
            }

            if (! Schema::hasColumn('waitlist_items', 'ready_to_pay_immediately')) {
                $table->boolean('ready_to_pay_immediately')->default(false)->after('ready_to_book_immediately');
            }

            if (! Schema::hasColumn('waitlist_items', 'auto_send_request')) {
                $table->boolean('auto_send_request')->default(false)->after('auto_request');
            }

            if (! Schema::hasColumn('waitlist_items', 'auto_create_booking_draft')) {
                $table->boolean('auto_create_booking_draft')->default(false)->after('auto_send_request');
            }

            if (! Schema::hasColumn('waitlist_items', 'notify_similar_available')) {
                $table->boolean('notify_similar_available')->default(false)->after('notify_price_drop');
            }

            if (! Schema::hasColumn('waitlist_items', 'notify_offer_expiring')) {
                $table->boolean('notify_offer_expiring')->default(true)->after('notify_similar_available');
            }

            if (! Schema::hasColumn('waitlist_items', 'quiet_hours_enabled')) {
                $table->boolean('quiet_hours_enabled')->default(true)->after('notify_offer_expiring');
            }

            if (! Schema::hasColumn('waitlist_items', 'quiet_hours_start')) {
                $table->string('quiet_hours_start', 5)->nullable()->after('quiet_hours_enabled');
            }

            if (! Schema::hasColumn('waitlist_items', 'quiet_hours_end')) {
                $table->string('quiet_hours_end', 5)->nullable()->after('quiet_hours_start');
            }

            if (! Schema::hasColumn('waitlist_items', 'guest_message')) {
                $table->text('guest_message')->nullable()->after('quiet_hours_end');
            }

            if (! Schema::hasColumn('waitlist_items', 'position')) {
                $table->unsignedInteger('position')->nullable()->after('guest_message');
            }

            if (! Schema::hasColumn('waitlist_items', 'priority_score')) {
                $table->integer('priority_score')->default(0)->after('position');
            }

            if (! Schema::hasColumn('waitlist_items', 'offered_count')) {
                $table->unsignedInteger('offered_count')->default(0)->after('priority_score');
            }

            if (! Schema::hasColumn('waitlist_items', 'skipped_count')) {
                $table->unsignedInteger('skipped_count')->default(0)->after('offered_count');
            }

            if (! Schema::hasColumn('waitlist_items', 'max_skips')) {
                $table->unsignedTinyInteger('max_skips')->default(3)->after('skipped_count');
            }

            if (! Schema::hasColumn('waitlist_items', 'last_offered_at')) {
                $table->timestamp('last_offered_at')->nullable()->after('max_skips');
            }

            if (! Schema::hasColumn('waitlist_items', 'last_notified_at')) {
                $table->timestamp('last_notified_at')->nullable()->after('last_offered_at');
            }

            if (! Schema::hasColumn('waitlist_items', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('last_notified_at');
            }

            if (! Schema::hasColumn('waitlist_items', 'last_checked_at')) {
                $table->timestamp('last_checked_at')->nullable()->after('expires_at');
            }

            if (! Schema::hasColumn('waitlist_items', 'added_at')) {
                $table->timestamp('added_at')->nullable()->after('last_checked_at');
            }

            if (! Schema::hasColumn('waitlist_items', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('added_at');
            }

            if (! Schema::hasColumn('waitlist_items', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('cancelled_at');
            }
        });

        Schema::table('waitlist_items', function (Blueprint $table): void {
            foreach ($this->waitlistItemIndexes() as $name => $columns) {
                if ($this->columnsExist('waitlist_items', $columns) && ! Schema::hasIndex('waitlist_items', $name)) {
                    $table->index($columns, $name);
                }
            }
        });

        if (! Schema::hasTable('waitlist_offers')) {
            Schema::create('waitlist_offers', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('waitlist_item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
                $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status')->default('active');
                $table->timestamp('offered_at')->nullable();
                $table->timestamp('offer_expires_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('declined_at')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->timestamp('skipped_at')->nullable();
                $table->decimal('current_price_per_night', 10, 2)->nullable();
                $table->decimal('current_total_price', 10, 2)->nullable();
                $table->decimal('current_deposit', 10, 2)->nullable();
                $table->string('currency', 3)->nullable();
                $table->timestamp('hold_started_at')->nullable();
                $table->timestamp('hold_expires_at')->nullable();
                $table->timestamp('notification_sent_at')->nullable();
                $table->text('guest_response_message')->nullable();
                $table->text('system_note')->nullable();
                $table->timestamps();

                $table->index(['waitlist_item_id', 'status'], 'waitlist_offers_item_status_index');
                $table->index(['user_id', 'status'], 'waitlist_offers_user_status_index');
                $table->index('property_id', 'waitlist_offers_property_id_index');
                $table->index('room_id', 'waitlist_offers_room_id_index');
                $table->index(['sleeping_place_id', 'status'], 'waitlist_offers_place_status_index');
                $table->index(['status', 'offer_expires_at'], 'waitlist_offers_status_expires_index');
                $table->index('booking_id', 'waitlist_offers_booking_id_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlist_offers');

        if (! Schema::hasTable('waitlist_items')) {
            return;
        }

        Schema::table('waitlist_items', function (Blueprint $table): void {
            foreach (array_reverse($this->waitlistItemIndexes()) as $name => $columns) {
                if ($this->columnsExist('waitlist_items', $columns) && Schema::hasIndex('waitlist_items', $name)) {
                    $table->dropIndex($name);
                }
            }
        });

        Schema::table('waitlist_items', function (Blueprint $table): void {
            foreach (array_reverse($this->addedColumns()) as $column) {
                if (Schema::hasColumn('waitlist_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * @return array<string, list<string>>
     */
    private function waitlistItemIndexes(): array
    {
        return [
            'waitlist_items_user_status_index' => ['user_id', 'status'],
            'waitlist_items_place_status_index' => ['sleeping_place_id', 'status'],
            'waitlist_items_place_dates_index' => ['sleeping_place_id', 'desired_check_in_date', 'desired_check_out_date'],
            'waitlist_items_property_status_index' => ['property_id', 'status'],
            'waitlist_items_room_status_index' => ['room_id', 'status'],
            'waitlist_items_status_expires_index' => ['status', 'expires_at'],
            'waitlist_items_status_checked_index' => ['status', 'last_checked_at'],
            'waitlist_items_status_priority_index' => ['status', 'priority_score'],
            'waitlist_items_desired_dates_index' => ['desired_check_in_date', 'desired_check_out_date'],
            'waitlist_items_notify_available_index' => ['notify_available'],
        ];
    }

    /**
     * @return list<string>
     */
    private function addedColumns(): array
    {
        return [
            'property_id',
            'room_id',
            'source',
            'desired_check_in_date',
            'desired_check_out_date',
            'nights_count',
            'calendar_days_count',
            'guests_count',
            'flexible_dates',
            'flexible_days',
            'min_nights',
            'max_nights',
            'max_price_per_night',
            'max_total_price',
            'max_deposit',
            'currency',
            'ready_to_book_immediately',
            'ready_to_pay_immediately',
            'auto_send_request',
            'auto_create_booking_draft',
            'notify_similar_available',
            'notify_offer_expiring',
            'quiet_hours_enabled',
            'quiet_hours_start',
            'quiet_hours_end',
            'guest_message',
            'position',
            'priority_score',
            'offered_count',
            'skipped_count',
            'max_skips',
            'last_offered_at',
            'last_notified_at',
            'expires_at',
            'last_checked_at',
            'added_at',
            'cancelled_at',
            'completed_at',
        ];
    }

    /**
     * @param  list<string>  $columns
     */
    private function columnsExist(string $table, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }
};
