<?php

namespace App\Services\BookingGuestIntake;

use App\Models\BookingGuestIntake;

class BookingGuestIntakeCompatibilityService
{
    public function __construct(
        private readonly BookingGuestIntakeValidationService $validation,
    ) {}

    /**
     * @return array{warnings:list<array{key:string,severity:string,message:string}>,blocking_reasons:list<array{key:string,severity:string,message:string}>,status:string,score:int}
     */
    public function checkAgainstPlace(BookingGuestIntake $intake): array
    {
        $result = $this->validation->inspect($intake);
        $warnings = $result['warnings'];
        $blockingReasons = $result['blocking_reasons'];
        $score = max(0, min(100, 100 - (count($warnings) * 8) - (count($blockingReasons) * 30)));

        return [
            'warnings' => $warnings,
            'blocking_reasons' => $blockingReasons,
            'status' => ($warnings !== [] || $blockingReasons !== []) ? 'needs_attention' : 'ready',
            'score' => $score,
        ];
    }

    /**
     * @return list<array{key:string,severity:string,message:string}>
     */
    public function checkEarlyLateRules(BookingGuestIntake $intake): array
    {
        return collect($this->validation->warnings($intake))
            ->whereIn('key', ['early_check_in_unavailable', 'late_check_out_unavailable'])
            ->values()
            ->all();
    }

    /**
     * @return list<array{key:string,severity:string,message:string}>
     */
    public function checkPetRules(BookingGuestIntake $intake): array
    {
        return collect($this->validation->blockingReasons($intake))
            ->where('key', 'pet_forbidden')
            ->values()
            ->all();
    }

    /**
     * @return list<array{key:string,severity:string,message:string}>
     */
    public function checkSmokingRules(BookingGuestIntake $intake): array
    {
        return collect($this->validation->warnings($intake))
            ->where('key', 'smoking_conflict')
            ->values()
            ->all();
    }

    /**
     * @return list<array{key:string,severity:string,message:string}>
     */
    public function checkDocumentNeeds(BookingGuestIntake $intake): array
    {
        return collect($this->validation->warnings($intake))
            ->where('key', 'documents_need_confirmation')
            ->values()
            ->all();
    }

    /**
     * @return list<array{key:string,severity:string,message:string}>
     */
    public function checkWorkspaceAndQuietNeeds(BookingGuestIntake $intake): array
    {
        return collect($this->validation->warnings($intake))
            ->whereIn('key', ['quiet_conflict', 'workspace_missing', 'fast_wifi_missing', 'power_socket_missing'])
            ->values()
            ->all();
    }
}
