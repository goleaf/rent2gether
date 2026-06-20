<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sleeping_places', function (Blueprint $table): void {
            if (! Schema::hasColumn('sleeping_places', 'extensions_allowed')) {
                $table->boolean('extensions_allowed')->default(true)->after('requires_host_approval');
            }
        });

        Schema::table('booking_extensions', function (Blueprint $table): void {
            if (! Schema::hasColumn('booking_extensions', 'current_checkout_date')) {
                $table->date('current_checkout_date')->nullable()->after('booking_id');
            }
            if (! Schema::hasColumn('booking_extensions', 'requested_new_checkout_date')) {
                $table->date('requested_new_checkout_date')->nullable()->after('current_checkout_date');
            }
            if (! Schema::hasColumn('booking_extensions', 'additional_nights')) {
                $table->unsignedSmallInteger('additional_nights')->default(0)->after('requested_new_checkout_date');
            }
            if (! Schema::hasColumn('booking_extensions', 'additional_amount')) {
                $table->decimal('additional_amount', 10, 2)->default(0)->after('additional_nights');
            }
            if (! Schema::hasColumn('booking_extensions', 'new_total')) {
                $table->decimal('new_total', 10, 2)->default(0)->after('total_extra');
            }
            if (! Schema::hasColumn('booking_extensions', 'payment_required')) {
                $table->boolean('payment_required')->default(false)->after('new_total');
            }
            if (! Schema::hasColumn('booking_extensions', 'payment_deadline_at')) {
                $table->timestamp('payment_deadline_at')->nullable()->after('payment_required');
            }
            if (! Schema::hasColumn('booking_extensions', 'guest_message')) {
                $table->text('guest_message')->nullable()->after('requires_host_approval');
            }
            if (! Schema::hasColumn('booking_extensions', 'host_response')) {
                $table->text('host_response')->nullable()->after('host_reply');
            }
            if (! Schema::hasColumn('booking_extensions', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('paid_at');
            }
            if (! Schema::hasColumn('booking_extensions', 'declined_at')) {
                $table->timestamp('declined_at')->nullable()->after('approved_at');
            }
            if (! Schema::hasColumn('booking_extensions', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('declined_at');
            }
        });

        Schema::table('booking_extensions', function (Blueprint $table): void {
            foreach ([
                ['booking_id', 'status'],
                ['booking_id', 'requested_new_checkout_date'],
                ['status', 'requested_new_checkout_date'],
            ] as $columns) {
                if ($this->columnsExist('booking_extensions', $columns) && ! Schema::hasIndex('booking_extensions', $columns)) {
                    $table->index($columns);
                }
            }
        });

        DB::table('booking_extensions')->where('status', 'pending')->update(['status' => 'awaiting_host_approval']);
        DB::table('booking_extensions')->where('status', 'rejected')->update(['status' => 'declined']);
        DB::table('booking_extensions')->where('status', 'paid')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('booking_extensions', function (Blueprint $table): void {
            foreach ([
                ['status', 'requested_new_checkout_date'],
                ['booking_id', 'requested_new_checkout_date'],
                ['booking_id', 'status'],
            ] as $columns) {
                if ($this->columnsExist('booking_extensions', $columns) && Schema::hasIndex('booking_extensions', $columns)) {
                    $table->dropIndex($columns);
                }
            }
        });

        Schema::table('booking_extensions', function (Blueprint $table): void {
            foreach ([
                'current_checkout_date',
                'requested_new_checkout_date',
                'additional_nights',
                'additional_amount',
                'new_total',
                'payment_required',
                'payment_deadline_at',
                'guest_message',
                'host_response',
                'approved_at',
                'declined_at',
                'cancelled_at',
            ] as $column) {
                if (Schema::hasColumn('booking_extensions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('sleeping_places', function (Blueprint $table): void {
            if (Schema::hasColumn('sleeping_places', 'extensions_allowed')) {
                $table->dropColumn('extensions_allowed');
            }
        });
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
