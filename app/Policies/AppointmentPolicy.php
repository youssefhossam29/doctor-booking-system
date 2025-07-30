<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Appointment;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view any appointments.
     */
    public function viewAny(User $user): bool
    {
        return $user->doctor !== null || $user->patient !== null;
    }

    /**
     * Determine whether the user can update a specific appointment.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->doctor) {
            return $user->doctor->id === $appointment->doctor_id;
        }

        if ($user->patient) {
            return $user->patient->id === $appointment->patient_id;
        }

        return false;
    }

    /**
     * Determine whether the user can cancel a specific appointment.
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        if ($user->doctor) {
            return $user->doctor->id === $appointment->doctor_id;
        }

        if ($user->patient) {
            return $user->patient->id === $appointment->patient_id;
        }

        return false;
    }

    /**
     * Determine whether the user can view a specific appointment.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->doctor) {
            return $user->doctor->id === $appointment->doctor_id;
        }

        if ($user->patient) {
            return $user->patient->id === $appointment->patient_id;
        }

        return false;
    }

}
