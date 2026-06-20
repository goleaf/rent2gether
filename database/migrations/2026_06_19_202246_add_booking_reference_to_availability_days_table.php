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
        Schema::table('availability_days', function (Blueprint $table): void {
            $table->foreignId('booking_id')
                ->nullable()
                ->after('sleeping_place_id')
                ->constrained()
                ->nullOnDelete();

            $table->index(['booking_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('availability_days', function (Blueprint $table): void {
            $table->dropIndex(['booking_id', 'status']);
            $table->dropConstrainedForeignId('booking_id');
        });
    }
};
