<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DoctorSlot;

class DoctorSlotPolicy
{
    /**
     * Determine whether the user can view any slots.
     */
    public function viewAny(User $user): bool
    {
        return $user->doctor !== null;
    }

    /**
     * Determine whether the user can view a single slot.
     */
    public function view(User $user, DoctorSlot $doctorSlot): bool
    {
        return $user->doctor && $user->doctor->id === $doctorSlot->doctor_id;
    }

    /**
     * Determine whether the user can delete all slots by date.
     */
    public function deleteByDate(User $user): bool
    {
        return $user->doctor !== null;
    }

    /**
     * Determine whether the user can delete a specific slot.
     */
    public function delete(User $user, DoctorSlot $doctorSlot): bool
    {
        return $user->doctor && $user->doctor->id === $doctorSlot->doctor_id;
    }
}
