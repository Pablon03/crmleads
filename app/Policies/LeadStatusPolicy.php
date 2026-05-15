<?php

namespace App\Policies;

use App\Models\LeadStatus;
use App\Models\User;

class LeadStatusPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LeadStatus $leadStatus): bool
    {
        return $user->id === $leadStatus->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, LeadStatus $leadStatus): bool
    {
        return $user->id === $leadStatus->user_id;
    }

    public function delete(User $user, LeadStatus $leadStatus): bool
    {
        return $user->id === $leadStatus->user_id;
    }

    public function restore(User $user, LeadStatus $leadStatus): bool
    {
        return $user->id === $leadStatus->user_id;
    }

    public function forceDelete(User $user, LeadStatus $leadStatus): bool
    {
        return $user->id === $leadStatus->user_id;
    }
}
