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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type'); // apartment, house, studio, hostel, guesthouse, etc.
            $table->text('description')->nullable();
            $table->string('country', 100);
            $table->string('city', 100);
            $table->string('district', 100)->nullable();
            $table->string('street', 200)->nullable();
            $table->string('building', 50)->nullable();
            $table->string('apartment', 50)->nullable();
            $table->unsignedTinyInteger('floor')->nullable();
            $table->boolean('has_elevator')->default(false);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->boolean('show_exact_address')->default(false); // shown only after confirmed booking
            $table->string('nearest_transport', 200)->nullable();
            $table->string('access_instructions')->nullable();
            $table->json('amenities')->nullable(); // wifi, kitchen, washer, etc.
            $table->json('rules')->nullable();
            $table->string('status')->default('draft'); // draft, active, hidden, suspended
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['city', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
