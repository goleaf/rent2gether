<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->index('rejected_by_user_id', 'bookings_rejected_by_user_id_index');
            $table->index('cancelled_by_user_id', 'bookings_cancelled_by_user_id_index');
        });

        Schema::table('booking_group_links', function (Blueprint $table): void {
            $table->index('property_id', 'booking_group_links_property_id_index');
            $table->index('room_id', 'booking_group_links_room_id_index');
        });

        Schema::table('booking_lifecycle_events', function (Blueprint $table): void {
            $table->index('user_id', 'booking_lifecycle_events_user_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex('bookings_rejected_by_user_id_index');
            $table->dropIndex('bookings_cancelled_by_user_id_index');
        });

        Schema::table('booking_lifecycle_events', function (Blueprint $table): void {
            $table->dropIndex('booking_lifecycle_events_user_id_index');
        });

        Schema::table('booking_group_links', function (Blueprint $table): void {
            $table->dropIndex('booking_group_links_property_id_index');
            $table->dropIndex('booking_group_links_room_id_index');
        });
    }
};
