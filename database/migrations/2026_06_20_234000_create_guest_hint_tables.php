<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_hint_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('hint_key');
            $table->string('category');
            $table->string('type');
            $table->string('importance');
            $table->integer('priority')->default(0);
            $table->string('message_key');
            $table->json('message_params_json')->nullable();
            $table->string('source')->nullable();
            $table->boolean('show_on_card')->default(false);
            $table->boolean('show_on_detail')->default(true);
            $table->boolean('show_before_booking')->default(false);
            $table->boolean('show_in_favorites')->default(false);
            $table->boolean('show_in_saved_search')->default(false);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['sleeping_place_id', 'category'], 'listing_hint_snapshots_place_category_index');
            $table->index(['sleeping_place_id', 'priority'], 'listing_hint_snapshots_place_priority_index');
            $table->index('property_id', 'listing_hint_snapshots_property_index');
            $table->index('room_id', 'listing_hint_snapshots_room_index');
            $table->index(['city_id', 'category'], 'listing_hint_snapshots_city_category_index');
            $table->index('hint_key', 'listing_hint_snapshots_hint_key_index');
            $table->index('expires_at', 'listing_hint_snapshots_expires_at_index');
            $table->index('show_on_card', 'listing_hint_snapshots_show_on_card_index');
            $table->index('show_on_detail', 'listing_hint_snapshots_show_on_detail_index');
            $table->index('show_before_booking', 'listing_hint_snapshots_show_before_booking_index');
        });

        Schema::create('guest_hint_dismissals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('hint_key');
            $table->string('context')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'hint_key'], 'guest_hint_dismissals_user_hint_index');
            $table->index(['user_id', 'sleeping_place_id'], 'guest_hint_dismissals_user_place_index');
            $table->index('sleeping_place_id', 'guest_hint_dismissals_place_index');
            $table->index('expires_at', 'guest_hint_dismissals_expires_at_index');
        });

        Schema::create('guest_hint_impressions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sleeping_place_id')->constrained()->cascadeOnDelete();
            $table->string('hint_key');
            $table->string('context')->nullable();
            $table->timestamp('shown_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'shown_at'], 'guest_hint_impressions_user_shown_index');
            $table->index(['sleeping_place_id', 'hint_key'], 'guest_hint_impressions_place_hint_index');
            $table->index(['hint_key', 'shown_at'], 'guest_hint_impressions_hint_shown_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_hint_impressions');
        Schema::dropIfExists('guest_hint_dismissals');
        Schema::dropIfExists('listing_hint_snapshots');
    }
};
