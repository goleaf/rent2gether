<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            if (! Schema::hasColumn('reviews', 'guest_user_id')) {
                $table->foreignId('guest_user_id')->nullable()->after('reviewee_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('reviews', 'host_user_id')) {
                $table->foreignId('host_user_id')->nullable()->after('guest_user_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('reviews', 'sleeping_place_comfort_rating')) {
                $table->tinyInteger('sleeping_place_comfort_rating')->nullable()->after('bed_comfort_rating');
            }

            if (! Schema::hasColumn('reviews', 'host_communication_rating')) {
                $table->tinyInteger('host_communication_rating')->nullable()->after('communication_rating');
            }

            if (! Schema::hasColumn('reviews', 'rule_following_rating')) {
                $table->tinyInteger('rule_following_rating')->nullable()->after('rule_compliance_rating');
            }

            if (! Schema::hasColumn('reviews', 'respect_rating')) {
                $table->tinyInteger('respect_rating')->nullable()->after('punctuality_rating');
            }

            if (! Schema::hasColumn('reviews', 'liked_text')) {
                $table->text('liked_text')->nullable()->after('would_return');
                $table->text('improvement_text')->nullable()->after('liked_text');
                $table->text('advice_text')->nullable()->after('improvement_text');
                $table->text('comment')->nullable()->after('advice_text');
                $table->json('photos_json')->nullable()->after('comment');
                $table->boolean('recommend')->nullable()->after('photos_json');
                $table->boolean('recommend_guest')->nullable()->after('recommend');
                $table->timestamp('visible_at')->nullable()->after('status');
                $table->json('flagged_words_json')->nullable()->after('visible_at');
            }

            $table->index(['guest_user_id', 'type', 'status'], 'reviews_guest_type_status_index');
            $table->index(['host_user_id', 'type', 'status'], 'reviews_host_type_status_index');
            $table->index(['sleeping_place_id', 'status', 'visible_at', 'created_at'], 'reviews_place_status_visible_index');
            $table->index(['reviewee_id', 'type', 'status'], 'reviews_reviewee_type_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropIndex('reviews_guest_type_status_index');
            $table->dropIndex('reviews_host_type_status_index');
            $table->dropIndex('reviews_place_status_visible_index');
            $table->dropIndex('reviews_reviewee_type_status_index');

            if (Schema::hasColumn('reviews', 'guest_user_id')) {
                $table->dropConstrainedForeignId('guest_user_id');
            }

            if (Schema::hasColumn('reviews', 'host_user_id')) {
                $table->dropConstrainedForeignId('host_user_id');
            }

            if (Schema::hasColumn('reviews', 'sleeping_place_comfort_rating')) {
                $table->dropColumn('sleeping_place_comfort_rating');
            }

            if (Schema::hasColumn('reviews', 'host_communication_rating')) {
                $table->dropColumn('host_communication_rating');
            }

            if (Schema::hasColumn('reviews', 'rule_following_rating')) {
                $table->dropColumn('rule_following_rating');
            }

            if (Schema::hasColumn('reviews', 'respect_rating')) {
                $table->dropColumn('respect_rating');
            }

            if (Schema::hasColumn('reviews', 'liked_text')) {
                $table->dropColumn([
                    'liked_text',
                    'improvement_text',
                    'advice_text',
                    'comment',
                    'photos_json',
                    'recommend',
                    'recommend_guest',
                    'visible_at',
                    'flagged_words_json',
                ]);
            }
        });
    }
};
