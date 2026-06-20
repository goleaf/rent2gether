<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_item_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_item_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 12);
            $table->string('caption')->nullable();
            $table->timestamps();

            $table->unique(['media_item_id', 'locale']);
            $table->index(['locale', 'caption']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_item_translations');
    }
};
