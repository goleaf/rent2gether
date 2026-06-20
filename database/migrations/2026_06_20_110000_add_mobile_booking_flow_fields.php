<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if (Schema::hasColumn('bookings', 'bed_id')) {
                $table->foreignId('bed_id')->nullable()->change();
            }

            if (! Schema::hasColumn('bookings', 'arrival_time')) {
                $table->time('arrival_time')->nullable()->after('check_out_time');
            }

            if (! Schema::hasColumn('bookings', 'rules_accepted_at')) {
                $table->timestamp('rules_accepted_at')->nullable()->after('guest_message');
            }

            if (! Schema::hasColumn('bookings', 'availability_hold_expires_at')) {
                $table->timestamp('availability_hold_expires_at')->nullable()->after('payment_deadline_at');
            }

            $table->index(
                ['sleeping_place_id', 'status', 'availability_hold_expires_at'],
                'bookings_place_status_hold_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if (Schema::hasIndex('bookings', 'bookings_place_status_hold_index')) {
                $table->dropIndex('bookings_place_status_hold_index');
            }

            foreach (['availability_hold_expires_at', 'rules_accepted_at', 'arrival_time'] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('bookings', 'bed_id')) {
                $table->foreignId('bed_id')->nullable(false)->change();
            }
        });
    }
};
