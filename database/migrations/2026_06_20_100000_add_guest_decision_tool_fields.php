<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table): void {
            if (! Schema::hasColumn('favorites', 'check_in')) {
                $table->date('check_in')->nullable()->after('price_at_save');
            }

            if (! Schema::hasColumn('favorites', 'check_out')) {
                $table->date('check_out')->nullable()->after('check_in');
            }

            if (! Schema::hasColumn('favorites', 'guests_count')) {
                $table->unsignedSmallInteger('guests_count')->default(1)->after('check_out');
            }

            if (! Schema::hasColumn('favorites', 'priority')) {
                $table->unsignedSmallInteger('priority')->default(0)->after('note');
            }

            $table->index(['user_id', 'priority'], 'favorites_user_priority_index');
        });

        Schema::table('saved_searches', function (Blueprint $table): void {
            if (! Schema::hasColumn('saved_searches', 'flexible_dates')) {
                $table->boolean('flexible_dates')->default(false)->after('check_out');
            }

            if (! Schema::hasColumn('saved_searches', 'currency')) {
                $table->string('currency', 3)->default('EUR')->after('price_max');
            }

            $table->index(['user_id', 'is_active'], 'saved_searches_user_active_index');
        });

        Schema::table('waitlist_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('waitlist_items', 'price_at_join')) {
                $table->decimal('price_at_join', 10, 2)->nullable()->after('max_price');
            }

            if (! Schema::hasColumn('waitlist_items', 'notify_available')) {
                $table->boolean('notify_available')->default(true)->after('auto_request');
            }

            if (! Schema::hasColumn('waitlist_items', 'notify_price_drop')) {
                $table->boolean('notify_price_drop')->default(true)->after('notify_available');
            }

            $table->index(
                ['sleeping_place_id', 'status', 'desired_check_in', 'desired_check_out'],
                'waitlist_place_status_dates_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('waitlist_items', function (Blueprint $table): void {
            $table->dropIndex('waitlist_place_status_dates_index');

            foreach (['notify_price_drop', 'notify_available', 'price_at_join'] as $column) {
                if (Schema::hasColumn('waitlist_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('saved_searches', function (Blueprint $table): void {
            $table->dropIndex('saved_searches_user_active_index');

            foreach (['currency', 'flexible_dates'] as $column) {
                if (Schema::hasColumn('saved_searches', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('favorites', function (Blueprint $table): void {
            $table->dropIndex('favorites_user_priority_index');

            foreach (['priority', 'guests_count', 'check_out', 'check_in'] as $column) {
                if (Schema::hasColumn('favorites', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
