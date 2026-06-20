<?php

namespace App\Models;

use Database\Factories\BookingGuestIntakeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingGuestIntake extends Model
{
    /** @use HasFactory<BookingGuestIntakeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_user_id',
        'booking_quote_id',
        'booking_request_id',
        'booking_id',
        'property_id',
        'room_id',
        'sleeping_place_id',
        'status',
        'trip_purpose',
        'trip_purpose_other',
        'trip_purpose_visibility',
        'planned_arrival_date',
        'planned_arrival_time',
        'planned_arrival_window',
        'planned_departure_time',
        'needs_early_check_in',
        'needs_late_check_out',
        'luggage_amount',
        'arrival_time_unknown',
        'departure_time_unknown',
        'early_check_in_requested',
        'requested_early_check_in_time',
        'late_check_in_requested',
        'requested_late_check_in_time',
        'late_check_out_requested',
        'requested_late_check_out_time',
        'early_check_out_requested',
        'requested_early_check_out_time',
        'can_adjust_arrival_time',
        'baggage_level',
        'baggage_count',
        'has_large_suitcase',
        'has_special_baggage',
        'special_baggage_type',
        'needs_luggage_storage_before_checkin',
        'needs_luggage_storage_after_checkout',
        'has_pet',
        'pet_type',
        'pet_size',
        'pet_notes',
        'smokes',
        'smoking_type',
        'accepts_smoking_rules',
        'needs_quiet',
        'needs_desk',
        'noise_sensitivity_level',
        'needs_workspace',
        'needs_fast_wifi',
        'needs_power_socket',
        'needs_online_calls',
        'needs_late_entry',
        'needs_self_check_in',
        'needs_registration',
        'needs_work_documents',
        'needs_invoice',
        'needs_receipt',
        'needs_contract',
        'company_name',
        'document_notes',
        'special_requests',
        'message_to_host',
        'host_message',
        'auto_generated_host_message',
        'rules_accepted',
        'rules_accepted_at',
        'compatibility_checked_at',
        'compatibility_status',
        'compatibility_score',
        'warnings_json',
        'blocking_reasons_json',
    ];

    protected function casts(): array
    {
        return [
            'planned_arrival_date' => 'date',
            'needs_early_check_in' => 'boolean',
            'needs_late_check_out' => 'boolean',
            'arrival_time_unknown' => 'boolean',
            'departure_time_unknown' => 'boolean',
            'early_check_in_requested' => 'boolean',
            'late_check_in_requested' => 'boolean',
            'late_check_out_requested' => 'boolean',
            'early_check_out_requested' => 'boolean',
            'can_adjust_arrival_time' => 'boolean',
            'baggage_count' => 'integer',
            'has_large_suitcase' => 'boolean',
            'has_special_baggage' => 'boolean',
            'needs_luggage_storage_before_checkin' => 'boolean',
            'needs_luggage_storage_after_checkout' => 'boolean',
            'has_pet' => 'boolean',
            'smokes' => 'boolean',
            'accepts_smoking_rules' => 'boolean',
            'needs_quiet' => 'boolean',
            'needs_desk' => 'boolean',
            'needs_workspace' => 'boolean',
            'needs_fast_wifi' => 'boolean',
            'needs_power_socket' => 'boolean',
            'needs_online_calls' => 'boolean',
            'needs_late_entry' => 'boolean',
            'needs_self_check_in' => 'boolean',
            'needs_registration' => 'boolean',
            'needs_work_documents' => 'boolean',
            'needs_invoice' => 'boolean',
            'needs_receipt' => 'boolean',
            'needs_contract' => 'boolean',
            'rules_accepted' => 'boolean',
            'rules_accepted_at' => 'datetime',
            'compatibility_checked_at' => 'datetime',
            'compatibility_score' => 'integer',
            'warnings_json' => 'array',
            'blocking_reasons_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_user_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function sleepingPlace(): BelongsTo
    {
        return $this->belongsTo(SleepingPlace::class);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('status'), 'draft');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('status'), 'completed');
    }

    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where(function (Builder $builder) use ($userId): void {
            $builder
                ->where($builder->qualifyColumn('user_id'), $userId)
                ->orWhere($builder->qualifyColumn('guest_user_id'), $userId);
        });
    }

    public function scopeForBooking(Builder $query, Booking|int $booking): Builder
    {
        return $query->where($query->qualifyColumn('booking_id'), $booking instanceof Booking ? $booking->id : $booking);
    }

    public function scopeWithWarnings(Builder $query): Builder
    {
        return $query->whereNotNull($query->qualifyColumn('warnings_json'))
            ->where($query->qualifyColumn('warnings_json'), '!=', '[]');
    }

    public function scopeWithBlockingReasons(Builder $query): Builder
    {
        return $query->whereNotNull($query->qualifyColumn('blocking_reasons_json'))
            ->where($query->qualifyColumn('blocking_reasons_json'), '!=', '[]');
    }

    public function scopeNeedsHostApproval(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder
                ->where($builder->qualifyColumn('early_check_in_requested'), true)
                ->orWhere($builder->qualifyColumn('late_check_in_requested'), true)
                ->orWhere($builder->qualifyColumn('late_check_out_requested'), true)
                ->orWhere($builder->qualifyColumn('has_pet'), true)
                ->orWhere($builder->qualifyColumn('needs_registration'), true)
                ->orWhere($builder->qualifyColumn('needs_work_documents'), true)
                ->orWhere($builder->qualifyColumn('needs_invoice'), true)
                ->orWhere($builder->qualifyColumn('needs_contract'), true);
        });
    }

    public function scopeHasSpecialRequests(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder
                ->whereNotNull($builder->qualifyColumn('special_requests'))
                ->orWhereNotNull($builder->qualifyColumn('host_message'))
                ->orWhereNotNull($builder->qualifyColumn('trip_purpose_other'))
                ->orWhereNotNull($builder->qualifyColumn('pet_notes'))
                ->orWhereNotNull($builder->qualifyColumn('document_notes'));
        });
    }
}
