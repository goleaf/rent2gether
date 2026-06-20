<?php

namespace App\Policies;

use App\Models\SavedSearch;
use App\Models\User;

class SavedSearchPolicy
{
    public function view(User $user, SavedSearch $savedSearch): bool
    {
        return (int) $savedSearch->user_id === (int) $user->id;
    }

    public function update(User $user, SavedSearch $savedSearch): bool
    {
        return $this->view($user, $savedSearch);
    }

    public function delete(User $user, SavedSearch $savedSearch): bool
    {
        return $this->view($user, $savedSearch);
    }
}
