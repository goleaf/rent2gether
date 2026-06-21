<?php

use App\Models\SleepingPlace;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sleeping_places', 'deposit_amount')) {
            return;
        }

        Schema::table('sleeping_places', function (Blueprint $table): void {
            $table->decimal('deposit_amount', 10, 2)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('sleeping_places', 'deposit_amount')) {
            return;
        }

        SleepingPlace::withTrashed()
            ->whereNull('deposit_amount')
            ->update(['deposit_amount' => 0]);

        Schema::table('sleeping_places', function (Blueprint $table): void {
            $table->decimal('deposit_amount', 10, 2)->default(0)->nullable(false)->change();
        });
    }
};
