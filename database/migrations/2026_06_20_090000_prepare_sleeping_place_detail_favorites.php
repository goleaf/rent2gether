<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('favorites')) {
            return;
        }

        Schema::table('favorites', function (Blueprint $table): void {
            if (Schema::hasColumn('favorites', 'bed_id')) {
                $table->foreignId('bed_id')->nullable()->change();
            }

            if (
                Schema::hasColumn('favorites', 'user_id')
                && Schema::hasColumn('favorites', 'sleeping_place_id')
                && ! Schema::hasIndex('favorites', 'favorites_user_sleeping_place_unique')
            ) {
                $table->unique(['user_id', 'sleeping_place_id'], 'favorites_user_sleeping_place_unique');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('favorites')) {
            return;
        }

        Schema::table('favorites', function (Blueprint $table): void {
            if (Schema::hasIndex('favorites', 'favorites_user_sleeping_place_unique')) {
                $table->dropUnique('favorites_user_sleeping_place_unique');
            }
        });
    }
};
