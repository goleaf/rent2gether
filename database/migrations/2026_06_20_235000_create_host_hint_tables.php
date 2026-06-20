<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('host_hint_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('hint_key');
            $table->string('category');
            $table->string('type');
            $table->string('importance');
            $table->integer('priority')->default(0);
            $table->string('message_key');
            $table->json('message_params_json')->nullable();
            $table->string('action_key')->nullable();
            $table->string('action_url')->nullable();
            $table->string('status')->default('active');
            $table->string('source')->nullable();
            $table->boolean('show_in_wizard')->default(true);
            $table->boolean('show_in_dashboard')->default(true);
            $table->boolean('show_before_publish')->default(false);
            $table->boolean('show_on_listing_card')->default(false);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'host_hint_snapshots_user_status_index');
            $table->index(['user_id', 'importance'], 'host_hint_snapshots_user_importance_index');
            $table->index(['property_id', 'status'], 'host_hint_snapshots_property_status_index');
            $table->index(['room_id', 'status'], 'host_hint_snapshots_room_status_index');
            $table->index(['sleeping_place_id', 'status'], 'host_hint_snapshots_place_status_index');
            $table->index('hint_key', 'host_hint_snapshots_hint_key_index');
            $table->index('category', 'host_hint_snapshots_category_index');
            $table->index('priority', 'host_hint_snapshots_priority_index');
            $table->index('expires_at', 'host_hint_snapshots_expires_at_index');
            $table->index('show_in_wizard', 'host_hint_snapshots_show_wizard_index');
            $table->index('show_in_dashboard', 'host_hint_snapshots_show_dashboard_index');
            $table->index('show_before_publish', 'host_hint_snapshots_show_before_publish_index');
        });

        Schema::create('host_hint_dismissals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('sleeping_place_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('hint_key');
            $table->string('context')->nullable();
            $table->timestamp('dismissed_until')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'hint_key'], 'host_hint_dismissals_user_hint_index');
            $table->index(['property_id', 'hint_key'], 'host_hint_dismissals_property_hint_index');
            $table->index(['room_id', 'hint_key'], 'host_hint_dismissals_room_hint_index');
            $table->index(['sleeping_place_id', 'hint_key'], 'host_hint_dismissals_place_hint_index');
            $table->index('dismissed_until', 'host_hint_dismissals_dismissed_until_index');
        });

        Schema::create('host_hint_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('host_hint_snapshot_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->string('action_status')->default('done');
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'acted_at'], 'host_hint_actions_user_acted_index');
            $table->index('host_hint_snapshot_id', 'host_hint_actions_snapshot_index');
            $table->index('action_status', 'host_hint_actions_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('host_hint_actions');
        Schema::dropIfExists('host_hint_dismissals');
        Schema::dropIfExists('host_hint_snapshots');
    }
};
