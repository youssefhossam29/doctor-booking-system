<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Patient;

class PatientPolicy
{
    /**
     * Determine if the authenticated doctor has any appointment with this patient.
     */

    public function view(User $user, Patient $patient): bool
    {
        return $user->doctor && $user->doctor->appointments()
            ->where('patient_id', $patient->id)
            ->exists();
    }
}
