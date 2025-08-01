<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContactPolicy
{

    public function view(User $user, Contact $contact): bool
    {
        return $user->id === $contact->user_id;
    }

    public function reply(User $user, Contact $contact): bool
    {
        return $user->id === $contact->user_id;
    }

    public function replies(User $user, Contact $contact): bool
    {
        return $user->id === $contact->user_id;
    }

}
