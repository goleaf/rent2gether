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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'host_experience_started_year')) {
                $table->unsignedSmallInteger('host_experience_started_year')
                    ->nullable()
                    ->after('host_experience_years');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'host_experience_started_year')) {
                $table->dropColumn('host_experience_started_year');
            }
        });
    }
};
