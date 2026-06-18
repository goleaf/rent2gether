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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('gender_type')->default('mixed'); // male, female, mixed
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('capacity');
            $table->decimal('area_sqm', 5, 1)->nullable();
            $table->boolean('has_lock')->default(false);
            $table->boolean('has_window')->default(true);
            $table->boolean('has_wardrobe')->default(false);
            $table->boolean('has_desk')->default(false);
            $table->boolean('has_ac')->default(false);
            $table->boolean('has_heating')->default(true);
            $table->boolean('has_balcony')->default(false);
            $table->json('rules')->nullable();
            $table->string('status')->default('active'); // active, maintenance, closed
            $table->timestamps();

            $table->index(['property_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
