<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('properties', 'rental_unit_type')) {
                $table->string('rental_unit_type')->nullable()->after('host_user_id');
            }

            if (! Schema::hasColumn('properties', 'region_name')) {
                $table->string('region_name')->nullable()->after('region_id');
            }
        });

        Schema::table('properties', function (Blueprint $table): void {
            if (Schema::hasColumn('properties', 'rental_unit_type') && ! Schema::hasIndex('properties', ['rental_unit_type', 'status'])) {
                $table->index(['rental_unit_type', 'status']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            if (Schema::hasColumn('properties', 'rental_unit_type') && Schema::hasIndex('properties', ['rental_unit_type', 'status'])) {
                $table->dropIndex(['rental_unit_type', 'status']);
            }

            $columns = collect(['region_name', 'rental_unit_type'])
                ->filter(fn (string $column): bool => Schema::hasColumn('properties', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
