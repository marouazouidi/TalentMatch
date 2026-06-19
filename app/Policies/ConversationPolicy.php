<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->analysis->jobOffer->user_id;
    }

    public function create(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
