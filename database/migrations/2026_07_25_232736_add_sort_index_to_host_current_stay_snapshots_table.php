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
        Schema::table('host_current_stay_snapshots', function (Blueprint $table) {
            $table->index(
                ['user_id', 'check_out_date', 'room_label', 'sleeping_place_label', 'id'],
                'current_stays_user_checkout_sort_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('host_current_stay_snapshots', function (Blueprint $table) {
            $table->dropIndex('current_stays_user_checkout_sort_index');
        });
    }
};
