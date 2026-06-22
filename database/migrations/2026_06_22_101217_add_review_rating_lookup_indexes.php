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
        if (Schema::hasTable('review_scores') && ! Schema::hasIndex('review_scores', 'review_scores_metric_public_review_idx')) {
            Schema::table('review_scores', function (Blueprint $table): void {
                $table->index(['score_key', 'is_public', 'review_id'], 'review_scores_metric_public_review_idx');
            });
        }

        if (! Schema::hasTable('reviews')) {
            return;
        }

        $indexes = [
            'reviews_sleeping_place_rating_lookup_idx' => ['sleeping_place_id', 'status', 'is_public', 'type'],
            'reviews_room_rating_lookup_idx' => ['room_id', 'status', 'is_public', 'type'],
            'reviews_property_rating_lookup_idx' => ['property_id', 'status', 'is_public', 'type'],
            'reviews_target_rating_lookup_idx' => ['target_user_id', 'status', 'is_public', 'type'],
        ];

        foreach ($indexes as $name => $columns) {
            if (! Schema::hasIndex('reviews', $name)) {
                Schema::table('reviews', function (Blueprint $table) use ($columns, $name): void {
                    $table->index($columns, $name);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('reviews')) {
            foreach ([
                'reviews_sleeping_place_rating_lookup_idx',
                'reviews_room_rating_lookup_idx',
                'reviews_property_rating_lookup_idx',
                'reviews_target_rating_lookup_idx',
            ] as $index) {
                if (Schema::hasIndex('reviews', $index)) {
                    Schema::table('reviews', function (Blueprint $table) use ($index): void {
                        $table->dropIndex($index);
                    });
                }
            }
        }

        if (Schema::hasTable('review_scores') && Schema::hasIndex('review_scores', 'review_scores_metric_public_review_idx')) {
            Schema::table('review_scores', function (Blueprint $table): void {
                $table->dropIndex('review_scores_metric_public_review_idx');
            });
        }
    }
};
