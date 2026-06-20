<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table): void {
            if (! Schema::hasColumn('complaints', 'complaint_number')) {
                $table->string('complaint_number')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('complaints', 'reporter_user_id')) {
                $table->foreignId('reporter_user_id')->nullable()->after('reference')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('complaints', 'priority')) {
                $table->string('priority')->default('normal')->after('type');
            }

            if (! Schema::hasColumn('complaints', 'refund_requested')) {
                $table->boolean('refund_requested')->default(false)->after('desired_resolution');
            }

            if (! Schema::hasColumn('complaints', 'deposit_hold_requested')) {
                $table->boolean('deposit_hold_requested')->default(false)->after('refund_requested');
            }

            if (! Schema::hasColumn('complaints', 'media')) {
                $table->json('media')->nullable()->after('deposit_hold_requested');
            }

            if (! Schema::hasColumn('complaints', 'other_side_response')) {
                $table->text('other_side_response')->nullable()->after('respondent_reply');
            }

            if (! Schema::hasColumn('complaints', 'resolution_text')) {
                $table->text('resolution_text')->nullable()->after('resolution_notes');
            }

            if (! Schema::hasColumn('complaints', 'deposit_hold_amount')) {
                $table->decimal('deposit_hold_amount', 10, 2)->nullable()->after('deposit_withheld');
            }

            $table->index(['booking_id', 'status'], 'complaints_booking_status_index');
            $table->index(['reporter_user_id', 'status'], 'complaints_reporter_user_status_index');
            $table->index(['reported_user_id', 'status'], 'complaints_reported_user_status_index');
            $table->index(['sleeping_place_id', 'status'], 'complaints_place_status_index');
        });

        Schema::create('complaint_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status');
            $table->string('note_key')->nullable();
            $table->text('note')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['complaint_id', 'created_at']);
            $table->index(['actor_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_status_histories');

        Schema::table('complaints', function (Blueprint $table): void {
            $table->dropIndex('complaints_booking_status_index');
            $table->dropIndex('complaints_reporter_user_status_index');
            $table->dropIndex('complaints_reported_user_status_index');
            $table->dropIndex('complaints_place_status_index');

            if (Schema::hasColumn('complaints', 'reporter_user_id')) {
                $table->dropConstrainedForeignId('reporter_user_id');
            }

            $table->dropColumn([
                'complaint_number',
                'priority',
                'refund_requested',
                'deposit_hold_requested',
                'media',
                'other_side_response',
                'resolution_text',
                'deposit_hold_amount',
            ]);
        });
    }
};
