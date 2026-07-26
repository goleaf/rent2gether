<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->indexIfMissing('properties', ['status', 'review_status'], 'properties_status_review_safety_idx');
        $this->indexIfMissing('properties', ['status', 'has_security'], 'properties_status_security_idx');
        $this->indexIfMissing('properties', ['status', 'has_cctv_common_areas'], 'properties_status_cctv_idx');
        $this->indexIfMissing('properties', ['show_exact_address_after_confirmation'], 'properties_exact_after_confirm_idx');
        $this->indexIfMissing('properties', ['show_exact_address_after_payment'], 'properties_exact_after_payment_idx');

        $this->indexIfMissing('property_addresses', ['show_exact_address_after_booking', 'property_id'], 'property_addr_exact_after_booking_idx');

        $this->indexIfMissing('property_access_details', ['has_key', 'property_id'], 'property_access_key_property_idx');
        $this->indexIfMissing('property_access_details', ['has_intercom', 'property_id'], 'property_access_intercom_property_idx');
        $this->indexIfMissing('property_access_details', ['has_intercom_code', 'property_id'], 'property_access_intercom_code_idx');
        $this->indexIfMissing('property_access_details', ['has_key_safe', 'property_id'], 'property_access_key_safe_property_idx');
        $this->indexIfMissing('property_access_details', ['emergency_contact_available', 'property_id'], 'property_access_emergency_property_idx');

        $this->indexIfMissing('host_profiles', ['verified_host', 'user_id'], 'host_profiles_verified_user_idx');
        $this->indexIfMissing('host_profiles', ['response_time_minutes', 'user_id'], 'host_profiles_response_user_idx');
        $this->indexIfMissing('host_profiles', ['emergency_contact_available', 'user_id'], 'host_profiles_emergency_user_idx');

        $this->indexIfMissing('sleeping_place_rating_snapshots', ['safety_rating', 'reviews_count'], 'place_rating_safety_reviews_idx');
        $this->indexIfMissing('room_rating_snapshots', ['safety_rating', 'reviews_count'], 'room_rating_safety_reviews_idx');
        $this->indexIfMissing('property_rating_snapshots', ['safety_rating', 'reviews_count'], 'property_rating_safety_reviews_idx');

        $this->indexIfMissing('complaint_cases', ['sleeping_place_id', 'status', 'complaint_type'], 'case_place_status_type_idx');
        $this->indexIfMissing('complaint_cases', ['room_id', 'status', 'complaint_type'], 'case_room_status_type_idx');
        $this->indexIfMissing('complaint_cases', ['property_id', 'status', 'complaint_type'], 'case_property_status_type_idx');
        $this->indexIfMissing('complaint_cases', ['host_user_id', 'status', 'complaint_type'], 'case_host_status_type_idx');
        $this->indexIfMissing('complaint_cases', ['status', 'severity'], 'case_status_severity_idx');

        $this->indexIfMissing('complaints', ['sleeping_place_id', 'status', 'type'], 'complaints_place_status_type_idx');
        $this->indexIfMissing('complaints', ['room_id', 'status', 'type'], 'complaints_room_status_type_idx');
        $this->indexIfMissing('complaints', ['property_id', 'status', 'type'], 'complaints_property_status_type_idx');
        $this->indexIfMissing('complaints', ['reported_user_id', 'status', 'type'], 'complaints_reported_status_type_idx');
        $this->indexIfMissing('complaints', ['status', 'urgency'], 'complaints_status_urgency_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('complaints', ['status', 'urgency'], 'complaints_status_urgency_idx');
        $this->dropIndexIfExists('complaints', ['reported_user_id', 'status', 'type'], 'complaints_reported_status_type_idx');
        $this->dropIndexIfExists('complaints', ['property_id', 'status', 'type'], 'complaints_property_status_type_idx');
        $this->dropIndexIfExists('complaints', ['room_id', 'status', 'type'], 'complaints_room_status_type_idx');
        $this->dropIndexIfExists('complaints', ['sleeping_place_id', 'status', 'type'], 'complaints_place_status_type_idx');

        $this->dropIndexIfExists('complaint_cases', ['status', 'severity'], 'case_status_severity_idx');
        $this->dropIndexIfExists('complaint_cases', ['host_user_id', 'status', 'complaint_type'], 'case_host_status_type_idx');
        $this->dropIndexIfExists('complaint_cases', ['property_id', 'status', 'complaint_type'], 'case_property_status_type_idx');
        $this->dropIndexIfExists('complaint_cases', ['room_id', 'status', 'complaint_type'], 'case_room_status_type_idx');
        $this->dropIndexIfExists('complaint_cases', ['sleeping_place_id', 'status', 'complaint_type'], 'case_place_status_type_idx');

        $this->dropIndexIfExists('property_rating_snapshots', ['safety_rating', 'reviews_count'], 'property_rating_safety_reviews_idx');
        $this->dropIndexIfExists('room_rating_snapshots', ['safety_rating', 'reviews_count'], 'room_rating_safety_reviews_idx');
        $this->dropIndexIfExists('sleeping_place_rating_snapshots', ['safety_rating', 'reviews_count'], 'place_rating_safety_reviews_idx');

        $this->dropIndexIfExists('host_profiles', ['emergency_contact_available', 'user_id'], 'host_profiles_emergency_user_idx');
        $this->dropIndexIfExists('host_profiles', ['response_time_minutes', 'user_id'], 'host_profiles_response_user_idx');
        $this->dropIndexIfExists('host_profiles', ['verified_host', 'user_id'], 'host_profiles_verified_user_idx');

        $this->dropIndexIfExists('property_access_details', ['emergency_contact_available', 'property_id'], 'property_access_emergency_property_idx');
        $this->dropIndexIfExists('property_access_details', ['has_key_safe', 'property_id'], 'property_access_key_safe_property_idx');
        $this->dropIndexIfExists('property_access_details', ['has_intercom_code', 'property_id'], 'property_access_intercom_code_idx');
        $this->dropIndexIfExists('property_access_details', ['has_intercom', 'property_id'], 'property_access_intercom_property_idx');
        $this->dropIndexIfExists('property_access_details', ['has_key', 'property_id'], 'property_access_key_property_idx');

        $this->dropIndexIfExists('property_addresses', ['show_exact_address_after_booking', 'property_id'], 'property_addr_exact_after_booking_idx');

        $this->dropIndexIfExists('properties', ['show_exact_address_after_payment'], 'properties_exact_after_payment_idx');
        $this->dropIndexIfExists('properties', ['show_exact_address_after_confirmation'], 'properties_exact_after_confirm_idx');
        $this->dropIndexIfExists('properties', ['status', 'has_cctv_common_areas'], 'properties_status_cctv_idx');
        $this->dropIndexIfExists('properties', ['status', 'has_security'], 'properties_status_security_idx');
        $this->dropIndexIfExists('properties', ['status', 'review_status'], 'properties_status_review_safety_idx');
    }

    /**
     * @param  list<string>  $columns
     */
    private function indexIfMissing(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || Schema::hasIndex($tableName, $columns)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName): void {
            $table->index($columns, $indexName);
        });
    }

    /**
     * @param  list<string>  $columns
     */
    private function dropIndexIfExists(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasIndex($tableName, $columns)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName): void {
            $table->dropIndex($indexName);
        });
    }
};
