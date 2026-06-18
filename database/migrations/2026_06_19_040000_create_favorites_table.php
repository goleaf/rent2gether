<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_id')->constrained()->cascadeOnDelete();
            $table->string('collection')->nullable();
            $table->text('note')->nullable();
            $table->decimal('price_at_save', 10, 2)->nullable();
            $table->boolean('notify_available')->default(false);
            $table->boolean('notify_price_drop')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'bed_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
