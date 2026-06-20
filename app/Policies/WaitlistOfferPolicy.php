<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WaitlistOffer;

class WaitlistOfferPolicy
{
    public function view(User $user, WaitlistOffer $offer): bool
    {
        return $offer->user_id === $user->id || $offer->property?->host_user_id === $user->id;
    }

    public function respond(User $user, WaitlistOffer $offer): bool
    {
        return $offer->user_id === $user->id;
    }
}
