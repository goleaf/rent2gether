<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX_NAME = 'sp_position_near_power_socket_index';

    public function up(): void
    {
        if (! Schema::hasTable('sleeping_place_position_details')
            || ! Schema::hasColumn('sleeping_place_position_details', 'near_power_socket')
            || Schema::hasIndex('sleeping_place_position_details', self::INDEX_NAME)
        ) {
            return;
        }

        Schema::table('sleeping_place_position_details', function (Blueprint $table): void {
            $table->index('near_power_socket', self::INDEX_NAME);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sleeping_place_position_details')
            || ! Schema::hasIndex('sleeping_place_position_details', self::INDEX_NAME)
        ) {
            return;
        }

        Schema::table('sleeping_place_position_details', function (Blueprint $table): void {
            $table->dropIndex(self::INDEX_NAME);
        });
    }
};
