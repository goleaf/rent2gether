<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->foreignId('host_id')->nullable()->after('guest_id')->constrained('users')->nullOnDelete();
            $table->foreignId('property_id')->nullable()->after('host_id')->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->after('property_id')->constrained()->nullOnDelete();
            $table->string('booking_type')->default('host_approval')->after('reference');
            $table->unsignedSmallInteger('calendar_days_count')->default(0)->after('nights');
            $table->decimal('tax_amount', 8, 2)->default(0)->after('service_fee');
            $table->decimal('city_fee_amount', 8, 2)->default(0)->after('tax_amount');
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->timestamp('payment_paid_at')->nullable()->after('payment_method');
            $table->timestamp('payment_deadline_at')->nullable()->after('payment_paid_at');
            $table->boolean('requires_document_check')->default(false)->after('payment_deadline_at');
            $table->boolean('requires_phone_check')->default(false)->after('requires_document_check');
            $table->boolean('requires_identity_check')->default(false)->after('requires_phone_check');
            $table->string('refund_status')->default('none')->after('refund_amount');
            $table->text('cancellation_terms')->nullable()->after('refund_status');
            $table->boolean('has_dispute')->default(false)->after('cancellation_terms');
            $table->boolean('has_complaint')->default(false)->after('has_dispute');
            $table->boolean('guest_review_left')->default(false)->after('has_complaint');
            $table->boolean('host_review_left')->default(false)->after('guest_review_left');
            $table->timestamp('review_deadline_at')->nullable()->after('host_review_left');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('host_id');
            $table->dropConstrainedForeignId('property_id');
            $table->dropConstrainedForeignId('room_id');
            $table->dropColumn([
                'booking_type',
                'calendar_days_count',
                'tax_amount',
                'city_fee_amount',
                'payment_method',
                'payment_paid_at',
                'payment_deadline_at',
                'requires_document_check',
                'requires_phone_check',
                'requires_identity_check',
                'refund_status',
                'cancellation_terms',
                'has_dispute',
                'has_complaint',
                'guest_review_left',
                'host_review_left',
                'review_deadline_at',
            ]);
        });
    }
};
