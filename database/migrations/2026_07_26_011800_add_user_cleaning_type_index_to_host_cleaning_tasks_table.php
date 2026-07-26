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
        Schema::table('host_cleaning_tasks', function (Blueprint $table): void {
            $table->index(['user_id', 'cleaning_type'], 'host_cleaning_tasks_user_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('host_cleaning_tasks', function (Blueprint $table): void {
            $table->dropIndex('host_cleaning_tasks_user_type_index');
        });
    }
};
