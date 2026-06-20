<?php

namespace App\Policies;

use App\Models\FavoriteCollection;
use App\Models\User;

class FavoriteCollectionPolicy
{
    public function view(User $user, FavoriteCollection $collection): bool
    {
        return (int) $collection->user_id === (int) $user->id;
    }

    public function update(User $user, FavoriteCollection $collection): bool
    {
        return $this->view($user, $collection);
    }

    public function delete(User $user, FavoriteCollection $collection): bool
    {
        return $this->view($user, $collection);
    }
}
