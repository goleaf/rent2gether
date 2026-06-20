<?php

namespace App\Policies;

use App\Models\Favorite;
use App\Models\User;

class FavoritePolicy
{
    public function view(User $user, Favorite $favorite): bool
    {
        return (int) $favorite->user_id === (int) $user->id;
    }

    public function update(User $user, Favorite $favorite): bool
    {
        return $this->view($user, $favorite);
    }

    public function delete(User $user, Favorite $favorite): bool
    {
        return $this->view($user, $favorite);
    }
}
