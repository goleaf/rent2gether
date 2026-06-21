<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendBookingExtensions();
        $this->createBookingExtensionLines();
        $this->createBookingExtensionValidationResults();
        $this->createBookingExtensionHostResponses();
        $this->createBookingExtensionGuestResponses();
        $this->createBookingExtensionStatusLogs();
        $this->createBookingExtensionEvents();
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_extension_events');
        Schema::dropIfExists('booking_extension_status_logs');
        Schema::dropIfExists('booking_extension_guest_responses');
        Schema::dropIfExists('booking_extension_host_responses');
        Schema::dropIfExists('booking_extension_validation_results');
        Schema::dropIfExists('booking_extension_lines');

        Schema::table('booking_extensions', function (Blueprint $table): void {
            foreach ([
                'booking_extensions_extension_number_unique',
                'booking_extensions_booking_id_status_p13_idx',
                'booking_extensions_booking_stay_id_p13_idx',
                'booking_extensions_guest_user_id_status_p13_idx',
                'booking_extensions_host_user_id_status_p13_idx',
                'booking_extensions_property_id_status_p13_idx',
                'booking_extensions_room_id_status_p13_idx',
                'booking_extensions_sleeping_place_id_status_p13_idx',
                'booking_extensions_booking_quote_id_p13_idx',
                'booking_extensions_booking_payment_id_p13_idx',
                'booking_extensions_current_checkout_p13_idx',
                'booking_extensions_new_checkout_p13_idx',
                'booking_extensions_status_p13_idx',
                'booking_extensions_payment_status_p13_idx',
                'booking_extensions_expires_at_p13_idx',
                'booking_extensions_hold_expires_at_p13_idx',
                'booking_extensions_applied_at_p13_idx',
            ] as $index) {
                if (Schema::hasIndex('booking_extensions', $index)) {
                    $table->dropIndex($index);
                }
            }
        });

        Schema::table('booking_extensions', function (Blueprint $table): void {
            foreach ([
                'extension_number',
                'booking_stay_id',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'booking_quote_id',
                'booking_payment_id',
                'current_check_out_date',
                'current_check_out_time',
                'new_check_out_date',
                'new_check_out_time',
                'additional_nights_count',
                'additional_chargeable_days_count',
                'additional_calendar_presence_days_count',
                'extension_type',
                'requires_host_confirmation',
                'requires_payment',
                'payment_status',
                'payment_method',
                'payment_deadline_at',
                'accommodation_amount',
                'service_fee_amount',
                'cleaning_fee_amount',
                'additional_deposit_amount',
                'total_payable',
                'host_payout_amount',
                'refundable_amount',
                'non_refundable_amount',
                'currency',
                'rejection_reason',
                'hold_dates',
                'hold_expires_at',
                'expires_at',
                'applied_at',
                'rejected_at',
                'closed_at',
            ] as $column) {
                if (Schema::hasColumn('booking_extensions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function extendBookingExtensions(): void
    {
        Schema::table('booking_extensions', function (Blueprint $table): void {
            $this->addString($table, 'extension_number');
            $this->addForeignId($table, 'booking_stay_id');
            $this->addForeignId($table, 'guest_user_id');
            $this->addForeignId($table, 'host_user_id');
            $this->addForeignId($table, 'property_id');
            $this->addForeignId($table, 'room_id');
            $this->addForeignId($table, 'sleeping_place_id');
            $this->addUnsignedBigInteger($table, 'booking_quote_id');
            $this->addForeignId($table, 'booking_payment_id');
            $this->addDate($table, 'current_check_out_date');
            $this->addString($table, 'current_check_out_time');
            $this->addDate($table, 'new_check_out_date');
            $this->addString($table, 'new_check_out_time');
            $this->addUnsignedInteger($table, 'additional_nights_count');
            $this->addUnsignedInteger($table, 'additional_chargeable_days_count');
            $this->addUnsignedInteger($table, 'additional_calendar_presence_days_count');
            $this->addStringWithDefault($table, 'extension_type', 'host_approval_extension');
            $this->addBoolean($table, 'requires_host_confirmation', true);
            $this->addBoolean($table, 'requires_payment', true);
            $this->addStringWithDefault($table, 'payment_status', 'unpaid');
            $this->addString($table, 'payment_method');
            $this->addTimestamp($table, 'payment_deadline_at');
            $this->addDecimal($table, 'accommodation_amount');
            $this->addDecimal($table, 'service_fee_amount');
            $this->addDecimal($table, 'cleaning_fee_amount');
            $this->addDecimal($table, 'additional_deposit_amount');
            $this->addDecimal($table, 'total_payable');
            $this->addDecimal($table, 'host_payout_amount');
            $this->addDecimal($table, 'refundable_amount');
            $this->addDecimal($table, 'non_refundable_amount');
            $this->addCurrency($table);
            $this->addText($table, 'rejection_reason');
            $this->addBoolean($table, 'hold_dates', true);
            $this->addTimestamp($table, 'hold_expires_at');
            $this->addTimestamp($table, 'expires_at');
            $this->addTimestamp($table, 'applied_at');
            $this->addTimestamp($table, 'rejected_at');
            $this->addTimestamp($table, 'closed_at');
        });

        Schema::table('booking_extensions', function (Blueprint $table): void {
            $this->addIndex($table, ['extension_number'], 'booking_extensions_extension_number_unique', unique: true);
            $this->addIndex($table, ['booking_id', 'status'], 'booking_extensions_booking_id_status_p13_idx');
            $this->addIndex($table, ['booking_stay_id'], 'booking_extensions_booking_stay_id_p13_idx');
            $this->addIndex($table, ['guest_user_id', 'status'], 'booking_extensions_guest_user_id_status_p13_idx');
            $this->addIndex($table, ['host_user_id', 'status'], 'booking_extensions_host_user_id_status_p13_idx');
            $this->addIndex($table, ['property_id', 'status'], 'booking_extensions_property_id_status_p13_idx');
            $this->addIndex($table, ['room_id', 'status'], 'booking_extensions_room_id_status_p13_idx');
            $this->addIndex($table, ['sleeping_place_id', 'status'], 'booking_extensions_sleeping_place_id_status_p13_idx');
            $this->addIndex($table, ['booking_quote_id'], 'booking_extensions_booking_quote_id_p13_idx');
            $this->addIndex($table, ['booking_payment_id'], 'booking_extensions_booking_payment_id_p13_idx');
            $this->addIndex($table, ['current_check_out_date'], 'booking_extensions_current_checkout_p13_idx');
            $this->addIndex($table, ['new_check_out_date'], 'booking_extensions_new_checkout_p13_idx');
            $this->addIndex($table, ['status'], 'booking_extensions_status_p13_idx');
            $this->addIndex($table, ['payment_status'], 'booking_extensions_payment_status_p13_idx');
            $this->addIndex($table, ['expires_at'], 'booking_extensions_expires_at_p13_idx');
            $this->addIndex($table, ['hold_expires_at'], 'booking_extensions_hold_expires_at_p13_idx');
            $this->addIndex($table, ['applied_at'], 'booking_extensions_applied_at_p13_idx');
        });
    }

    private function createBookingExtensionLines(): void
    {
        if (Schema::hasTable('booking_extension_lines')) {
            return;
        }

        Schema::create('booking_extension_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_extension_id')->constrained('booking_extensions')->cascadeOnDelete();
            $table->string('line_type');
            $table->string('label_key');
            $table->date('date')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_amount', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3);
            $table->boolean('is_discount')->default(false);
            $table->boolean('is_fee')->default(false);
            $table->boolean('is_deposit')->default(false);
            $table->boolean('is_refundable')->default(true);
            $table->boolean('is_payable_now')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['booking_extension_id', 'line_type'], 'booking_extension_lines_extension_type_idx');
            $table->index(['booking_extension_id', 'sort_order'], 'booking_extension_lines_extension_sort_idx');
            $table->index('date', 'booking_extension_lines_date_idx');
            $table->index('is_discount', 'booking_extension_lines_discount_idx');
            $table->index('is_deposit', 'booking_extension_lines_deposit_idx');
            $table->index('is_refundable', 'booking_extension_lines_refundable_idx');
        });
    }

    private function createBookingExtensionValidationResults(): void
    {
        if (Schema::hasTable('booking_extension_validation_results')) {
            return;
        }

        Schema::create('booking_extension_validation_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_extension_id')->constrained('booking_extensions')->cascadeOnDelete();
            $table->string('validation_key');
            $table->string('severity')->default('error');
            $table->string('message_key');
            $table->text('message_params_json')->nullable();
            $table->boolean('blocking')->default(true);
            $table->boolean('visible_to_guest')->default(true);
            $table->boolean('visible_to_host')->default(true);
            $table->timestamps();

            $table->index('booking_extension_id', 'booking_extension_validation_extension_idx');
            $table->index('validation_key', 'booking_extension_validation_key_idx');
            $table->index('blocking', 'booking_extension_validation_blocking_idx');
            $table->index('severity', 'booking_extension_validation_severity_idx');
            $table->index('visible_to_guest', 'booking_extension_validation_guest_idx');
            $table->index('visible_to_host', 'booking_extension_validation_host_idx');
        });
    }

    private function createBookingExtensionHostResponses(): void
    {
        if (Schema::hasTable('booking_extension_host_responses')) {
            return;
        }

        Schema::create('booking_extension_host_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_extension_id')->constrained('booking_extensions')->cascadeOnDelete();
            $table->foreignId('host_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('response_type');
            $table->text('message')->nullable();
            $table->date('proposed_new_check_out_date')->nullable();
            $table->string('proposed_new_check_out_time')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('booking_extension_id', 'booking_extension_host_responses_extension_idx');
            $table->index('host_user_id', 'booking_extension_host_responses_host_idx');
            $table->index('response_type', 'booking_extension_host_responses_type_idx');
        });
    }

    private function createBookingExtensionGuestResponses(): void
    {
        if (Schema::hasTable('booking_extension_guest_responses')) {
            return;
        }

        Schema::create('booking_extension_guest_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_extension_id')->constrained('booking_extensions')->cascadeOnDelete();
            $table->foreignId('guest_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('response_type');
            $table->text('message')->nullable();
            $table->date('accepted_new_check_out_date')->nullable();
            $table->string('accepted_new_check_out_time')->nullable();
            $table->timestamps();

            $table->index('booking_extension_id', 'booking_extension_guest_responses_extension_idx');
            $table->index('guest_user_id', 'booking_extension_guest_responses_guest_idx');
            $table->index('response_type', 'booking_extension_guest_responses_type_idx');
        });
    }

    private function createBookingExtensionStatusLogs(): void
    {
        if (Schema::hasTable('booking_extension_status_logs')) {
            return;
        }

        Schema::create('booking_extension_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_extension_id')->constrained('booking_extensions')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('reason_key')->nullable();
            $table->text('note')->nullable();
            $table->text('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_extension_id', 'booking_extension_status_logs_extension_idx');
            $table->index('booking_id', 'booking_extension_status_logs_booking_idx');
            $table->index('new_status', 'booking_extension_status_logs_status_idx');
            $table->index('user_id', 'booking_extension_status_logs_user_idx');
            $table->index('created_at', 'booking_extension_status_logs_created_idx');
        });
    }

    private function createBookingExtensionEvents(): void
    {
        if (Schema::hasTable('booking_extension_events')) {
            return;
        }

        Schema::create('booking_extension_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_extension_id')->constrained('booking_extensions')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('event_key');
            $table->string('event_type')->default('system');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->text('context_json')->nullable();
            $table->timestamps();

            $table->index('booking_extension_id', 'booking_extension_events_extension_idx');
            $table->index('booking_id', 'booking_extension_events_booking_idx');
            $table->index('event_key', 'booking_extension_events_key_idx');
            $table->index('event_type', 'booking_extension_events_type_idx');
            $table->index('user_id', 'booking_extension_events_user_idx');
            $table->index(['source_type', 'source_id'], 'booking_extension_events_source_idx');
            $table->index('occurred_at', 'booking_extension_events_occurred_idx');
        });
    }

    private function addString(Blueprint $table, string $column): void
    {
        if (! Schema::hasColumn('booking_extensions', $column)) {
            $table->string($column)->nullable();
        }
    }

    private function addStringWithDefault(Blueprint $table, string $column, string $default): void
    {
        if (! Schema::hasColumn('booking_extensions', $column)) {
            $table->string($column)->default($default);
        }
    }

    private function addText(Blueprint $table, string $column): void
    {
        if (! Schema::hasColumn('booking_extensions', $column)) {
            $table->text($column)->nullable();
        }
    }

    private function addForeignId(Blueprint $table, string $column): void
    {
        if (! Schema::hasColumn('booking_extensions', $column)) {
            $table->unsignedBigInteger($column)->nullable();
        }
    }

    private function addUnsignedBigInteger(Blueprint $table, string $column): void
    {
        if (! Schema::hasColumn('booking_extensions', $column)) {
            $table->unsignedBigInteger($column)->nullable();
        }
    }

    private function addDate(Blueprint $table, string $column): void
    {
        if (! Schema::hasColumn('booking_extensions', $column)) {
            $table->date($column)->nullable();
        }
    }

    private function addTimestamp(Blueprint $table, string $column): void
    {
        if (! Schema::hasColumn('booking_extensions', $column)) {
            $table->timestamp($column)->nullable();
        }
    }

    private function addUnsignedInteger(Blueprint $table, string $column): void
    {
        if (! Schema::hasColumn('booking_extensions', $column)) {
            $table->unsignedInteger($column)->default(0);
        }
    }

    private function addBoolean(Blueprint $table, string $column, bool $default): void
    {
        if (! Schema::hasColumn('booking_extensions', $column)) {
            $table->boolean($column)->default($default);
        }
    }

    private function addDecimal(Blueprint $table, string $column): void
    {
        if (! Schema::hasColumn('booking_extensions', $column)) {
            $table->decimal($column, 10, 2)->default(0);
        }
    }

    private function addCurrency(Blueprint $table): void
    {
        if (! Schema::hasColumn('booking_extensions', 'currency')) {
            $table->string('currency', 3)->default('EUR');
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function addIndex(Blueprint $table, array $columns, string $name, bool $unique = false): void
    {
        if (Schema::hasIndex('booking_extensions', $name)) {
            return;
        }

        $unique ? $table->unique($columns, $name) : $table->index($columns, $name);
    }
};
