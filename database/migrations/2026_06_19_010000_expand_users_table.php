<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->boolean('phone_verified')->default(false)->after('phone');
            $table->string('avatar')->nullable()->after('phone_verified');
            $table->date('date_of_birth')->nullable()->after('avatar');
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('country')->nullable()->after('gender');
            $table->string('city')->nullable()->after('country');
            $table->json('languages')->nullable()->after('city');
            $table->text('bio')->nullable()->after('languages');
            $table->string('occupation')->nullable()->after('bio');
            $table->string('travel_purpose')->nullable()->after('occupation');

            // Lifestyle preferences
            $table->boolean('is_smoker')->default(false)->after('travel_purpose');
            $table->boolean('has_pets')->default(false)->after('is_smoker');
            $table->boolean('has_allergies')->default(false)->after('has_pets');
            $table->boolean('prefers_quiet')->default(false)->after('has_allergies');
            $table->string('sleep_schedule')->nullable()->after('prefers_quiet'); // early, late, flexible
            $table->boolean('willing_to_share_room')->default(true)->after('sleep_schedule');
            $table->string('preferred_room_gender')->nullable()->after('willing_to_share_room');

            // Identity verification
            $table->boolean('identity_verified')->default(false)->after('preferred_room_gender');
            $table->timestamp('identity_verified_at')->nullable()->after('identity_verified');

            // Host fields
            $table->boolean('is_host')->default(false)->after('identity_verified_at');
            $table->text('host_description')->nullable()->after('is_host');
            $table->integer('host_experience_years')->nullable()->after('host_description');
            $table->boolean('host_lives_on_site')->default(false)->after('host_experience_years');
            $table->string('preferred_contact_method')->nullable()->after('host_lives_on_site');

            // Trust
            $table->decimal('rating_as_guest', 3, 2)->nullable()->after('preferred_contact_method');
            $table->decimal('rating_as_host', 3, 2)->nullable()->after('rating_as_guest');
            $table->integer('completed_stays_count')->default(0)->after('rating_as_host');
            $table->integer('hosted_stays_count')->default(0)->after('completed_stays_count');
            $table->integer('cancellations_count')->default(0)->after('hosted_stays_count');
            $table->integer('complaints_count')->default(0)->after('cancellations_count');

            $table->string('status')->default('active')->after('complaints_count');
            $table->timestamp('last_active_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'phone_verified', 'avatar', 'date_of_birth', 'gender',
                'country', 'city', 'languages', 'bio', 'occupation', 'travel_purpose',
                'is_smoker', 'has_pets', 'has_allergies', 'prefers_quiet', 'sleep_schedule',
                'willing_to_share_room', 'preferred_room_gender',
                'identity_verified', 'identity_verified_at',
                'is_host', 'host_description', 'host_experience_years', 'host_lives_on_site',
                'preferred_contact_method',
                'rating_as_guest', 'rating_as_host', 'completed_stays_count', 'hosted_stays_count',
                'cancellations_count', 'complaints_count', 'status', 'last_active_at',
            ]);
        });
    }
};
