<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table): void {
            if ($this->hasFavoriteColumns(['user_id', 'added_at', 'created_at']) && ! Schema::hasIndex('favorites', 'favorites_user_added_index')) {
                $table->index(['user_id', 'added_at', 'created_at'], 'favorites_user_added_index');
            }

            if ($this->hasFavoriteColumns(['favorite_collection_id', 'added_at', 'created_at']) && ! Schema::hasIndex('favorites', 'favorites_collection_added_index')) {
                $table->index(['favorite_collection_id', 'added_at', 'created_at'], 'favorites_collection_added_index');
            }

            if ($this->hasFavoriteColumns(['favorite_collection_id', 'is_currently_available', 'added_at', 'created_at']) && ! Schema::hasIndex('favorites', 'favorites_collection_available_added_index')) {
                $table->index(['favorite_collection_id', 'is_currently_available', 'added_at', 'created_at'], 'favorites_collection_available_added_index');
            }

            if ($this->hasFavoriteColumns(['favorite_collection_id', 'price_changed', 'added_at', 'created_at']) && ! Schema::hasIndex('favorites', 'favorites_collection_price_changed_added_index')) {
                $table->index(['favorite_collection_id', 'price_changed', 'added_at', 'created_at'], 'favorites_collection_price_changed_added_index');
            }

            if ($this->hasFavoriteColumns(['favorite_collection_id', 'current_price_per_night', 'price_per_night_snapshot']) && ! Schema::hasIndex('favorites', 'favorites_collection_current_price_index')) {
                $table->index(['favorite_collection_id', 'current_price_per_night', 'price_per_night_snapshot'], 'favorites_collection_current_price_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table): void {
            foreach ([
                'favorites_collection_current_price_index',
                'favorites_collection_price_changed_added_index',
                'favorites_collection_available_added_index',
                'favorites_collection_added_index',
                'favorites_user_added_index',
            ] as $index) {
                if (Schema::hasIndex('favorites', $index)) {
                    $table->dropIndex($index);
                }
            }
        });
    }

    /**
     * @param  list<string>  $columns
     */
    private function hasFavoriteColumns(array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn('favorites', $column)) {
                return false;
            }
        }

        return true;
    }
};
