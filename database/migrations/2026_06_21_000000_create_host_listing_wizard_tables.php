<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('host_listing_wizard_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('current_step')->default('property');
            $table->json('completed_steps_json')->nullable();
            $table->json('skipped_steps_json')->nullable();
            $table->timestamp('last_saved_at')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['user_id', 'status'], 'host_listing_sessions_user_status_index');
            $table->index('property_id', 'host_listing_sessions_property_index');
            $table->index('current_step', 'host_listing_sessions_step_index');
            $table->index('last_saved_at', 'host_listing_sessions_last_saved_index');
        });

        Schema::create('listing_publication_checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('check_key');
            $table->string('category');
            $table->string('severity');
            $table->string('status')->default('open');
            $table->string('message_key');
            $table->json('message_params_json')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_blocking')->default(false);
            $table->timestamp('fixed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'listing_checks_user_status_index');
            $table->index(['property_id', 'status'], 'listing_checks_property_status_index');
            $table->index(['room_id', 'status'], 'listing_checks_room_status_index');
            $table->index(['sleeping_place_id', 'status'], 'listing_checks_place_status_index');
            $table->index('check_key', 'listing_checks_key_index');
            $table->index('category', 'listing_checks_category_index');
            $table->index('severity', 'listing_checks_severity_index');
            $table->index('is_required', 'listing_checks_required_index');
            $table->index('is_blocking', 'listing_checks_blocking_index');
        });

        Schema::table('properties', function (Blueprint $table): void {
            $table->string('publication_status')->default('draft');
            $table->string('review_status')->nullable();
            $table->timestamp('review_requested_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_comment')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('archived_at')->nullable();

            $table->index('publication_status', 'properties_publication_status_index');
            $table->index('review_status', 'properties_review_status_index');
            $table->index('published_at', 'properties_published_at_index');
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->string('publication_status')->default('draft');
            $table->timestamp('completed_at')->nullable();

            $table->index('publication_status', 'rooms_publication_status_index');
        });

        Schema::table('sleeping_places', function (Blueprint $table): void {
            $table->unsignedTinyInteger('cleaning_gap_days')->default(0);
            $table->string('publication_status')->default('draft');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->index('cleaning_gap_days', 'sleeping_places_cleaning_gap_days_index');
            $table->index('publication_status', 'sleeping_places_publication_status_index');
            $table->index('published_at', 'sleeping_places_published_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('sleeping_places', function (Blueprint $table): void {
            $table->dropIndex('sleeping_places_cleaning_gap_days_index');
            $table->dropIndex('sleeping_places_publication_status_index');
            $table->dropIndex('sleeping_places_published_at_index');
            $table->dropColumn(['cleaning_gap_days', 'publication_status', 'completed_at', 'published_at']);
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropIndex('rooms_publication_status_index');
            $table->dropColumn(['publication_status', 'completed_at']);
        });

        Schema::table('properties', function (Blueprint $table): void {
            $table->dropIndex('properties_publication_status_index');
            $table->dropIndex('properties_review_status_index');
            $table->dropIndex('properties_published_at_index');
            $table->dropColumn([
                'publication_status',
                'review_status',
                'review_requested_at',
                'reviewed_at',
                'review_comment',
                'rejection_reason',
                'published_at',
                'paused_at',
                'archived_at',
            ]);
        });

        Schema::dropIfExists('listing_publication_checks');
        Schema::dropIfExists('host_listing_wizard_sessions');
    }
};
