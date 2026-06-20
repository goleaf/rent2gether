<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserLanguage;

class UserLanguageService
{
    public function add(User $user, string $languageCode, string $level = 'basic', bool $isPrimary = false): UserLanguage
    {
        if ($isPrimary) {
            UserLanguage::query()
                ->where('user_id', $user->id)
                ->update(['is_primary' => false]);
        }

        return UserLanguage::query()->updateOrCreate(
            ['user_id' => $user->id, 'language_code' => $languageCode],
            ['level' => $level, 'is_primary' => $isPrimary],
        );
    }
}
