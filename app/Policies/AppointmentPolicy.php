<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Appointment;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view any slots.
     */
    public function viewAny(User $user): bool
    {
        return $user->doctor !== null;
    }

    /**
     * Determine whether the user can update a specific appointment.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        return $user->doctor && $user->doctor->id === $appointment->doctor_id;
    }

    /**
     * Determine whether the user can delete a specific appointment.
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        return $user->doctor && $user->doctor->id === $appointment->doctor_id;
    }

    /**
     * Determine whether the user can view a specific appointment.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        return $user->doctor && $user->doctor->id === $appointment->doctor_id;
    }

}
