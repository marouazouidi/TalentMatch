<?php

namespace App\Policies;

use App\Models\JobOffer;
use App\Models\User;

class JobOfferPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, JobOffer $jobOffer): bool
    {
        return $user->id === $jobOffer->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, JobOffer $jobOffer): bool
    {
        return $user->id === $jobOffer->user_id;
    }

    public function delete(User $user, JobOffer $jobOffer): bool
    {
        return $user->id === $jobOffer->user_id;
    }
}
