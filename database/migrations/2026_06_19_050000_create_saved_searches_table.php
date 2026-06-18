<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->integer('nights')->nullable();
            $table->decimal('price_min', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable();
            $table->string('room_type')->nullable();
            $table->string('bed_type')->nullable();
            $table->json('amenities')->nullable();
            $table->json('filters')->nullable();
            $table->boolean('notify_new_places')->default(false);
            $table->boolean('notify_price_drop')->default(false);
            $table->boolean('notify_available')->default(false);
            $table->string('notify_frequency')->default('daily'); // instant, daily, weekly
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
