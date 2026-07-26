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
            if (Schema::hasIndex('waitlist_items', 'waitlist_items_user_id_sleeping_place_id_unique')) {
                $table->dropUnique('waitlist_items_user_id_sleeping_place_id_unique');
            }

            if (! Schema::hasIndex('waitlist_items', 'waitlist_items_user_place_dates_unique')) {
                $table->unique(
                    ['user_id', 'sleeping_place_id', 'desired_check_in_date', 'desired_check_out_date'],
                    'waitlist_items_user_place_dates_unique'
                );
            }

            if (! Schema::hasIndex('waitlist_items', 'waitlist_items_place_status_dates_queue_idx')) {
                $table->index(
                    [
                        'sleeping_place_id',
                        'status',
                        'desired_check_in_date',
                        'desired_check_out_date',
                        'priority_score',
                        'added_at',
                        'id',
                    ],
                    'waitlist_items_place_status_dates_queue_idx'
                );
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('waitlist_items')) {
            return;
        }

        Schema::table('waitlist_items', function (Blueprint $table): void {
            if (Schema::hasIndex('waitlist_items', 'waitlist_items_place_status_dates_queue_idx')) {
                $table->dropIndex('waitlist_items_place_status_dates_queue_idx');
            }

            if (Schema::hasIndex('waitlist_items', 'waitlist_items_user_place_dates_unique')) {
                $table->dropUnique('waitlist_items_user_place_dates_unique');
            }

            if (! Schema::hasIndex('waitlist_items', 'waitlist_items_user_id_sleeping_place_id_unique')) {
                $table->unique(['user_id', 'sleeping_place_id']);
            }
        });
    }
};
