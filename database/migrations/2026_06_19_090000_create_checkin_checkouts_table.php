<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkin_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->time('planned_time')->nullable();
            $table->timestamp('actual_arrival_at')->nullable();
            $table->string('met_by')->nullable();
            $table->boolean('keys_handed')->default(false);
            $table->boolean('room_shown')->default(false);
            $table->boolean('rules_explained')->default(false);
            $table->boolean('linen_provided')->default(false);
            $table->boolean('towel_provided')->default(false);
            $table->boolean('locker_assigned')->default(false);
            $table->json('photos_before')->nullable();
            $table->boolean('guest_confirmed')->default(false);
            $table->boolean('host_confirmed')->default(false);
            $table->boolean('has_issue')->default(false);
            $table->text('issue_description')->nullable();
            $table->json('issue_photos')->nullable();
            $table->string('status')->default('pending'); // pending, completed, problem
            $table->timestamps();
        });

        Schema::create('checkout_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->time('planned_time')->nullable();
            $table->timestamp('actual_departure_at')->nullable();
            $table->boolean('keys_returned')->default(false);
            $table->boolean('locker_emptied')->default(false);
            $table->boolean('belongings_collected')->default(false);
            $table->boolean('linen_returned')->default(false);
            $table->boolean('place_clean')->default(false);
            $table->boolean('has_damage')->default(false);
            $table->boolean('has_extra_dirt')->default(false);
            $table->boolean('has_forgotten_items')->default(false);
            $table->boolean('deposit_withheld')->default(false);
            $table->decimal('withhold_amount', 10, 2)->nullable();
            $table->string('withhold_reason')->nullable();
            $table->json('photos_after')->nullable();
            $table->boolean('guest_confirmed')->default(false);
            $table->boolean('host_confirmed')->default(false);
            $table->string('status')->default('pending'); // pending, completed, problem
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_records');
        Schema::dropIfExists('checkin_records');
    }
};
