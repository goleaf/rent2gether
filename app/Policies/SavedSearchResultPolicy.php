<?php

namespace App\Policies;

use App\Models\SavedSearchResult;
use App\Models\User;

class SavedSearchResultPolicy
{
    public function view(User $user, SavedSearchResult $savedSearchResult): bool
    {
        return (int) $savedSearchResult->savedSearch?->user_id === (int) $user->id;
    }
}
