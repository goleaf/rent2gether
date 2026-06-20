<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if (! Schema::hasIndex('bookings', 'bookings_host_payment_checkin_index')) {
                $table->index(['host_user_id', 'payment_status', 'check_in_date'], 'bookings_host_payment_checkin_index');
            }

            if (! Schema::hasIndex('bookings', 'bookings_host_checkin_index')) {
                $table->index(['host_user_id', 'check_in_date'], 'bookings_host_checkin_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if (Schema::hasIndex('bookings', 'bookings_host_payment_checkin_index')) {
                $table->dropIndex('bookings_host_payment_checkin_index');
            }

            if (Schema::hasIndex('bookings', 'bookings_host_checkin_index')) {
                $table->dropIndex('bookings_host_checkin_index');
            }
        });
    }
};
