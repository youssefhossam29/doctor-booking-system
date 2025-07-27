<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;
use App\Models\Doctor;

class SchedulePolicy
{
    /**
     * Determine whether the user can view any schedules.
     */
    public function viewAny(User $user): bool
    {
        //
        return $user->doctor !== null;
    }

    /**
     * Determine whether the user can create a schedule.
     */
    public function create(User $user): bool
    {
        return $user->doctor !== null;
    }

    /**
     * Determine whether the user can view the schedule.
     */
    public function view(User $user, Schedule $schedule): bool
    {
        return $user->doctor && $user->doctor->id === $schedule->doctor_id;
    }

    /**
     * Determine whether the user can update the schedule.
     */
    public function update(User $user, Schedule $schedule): bool
    {
        return $user->doctor && $user->doctor->id === $schedule->doctor_id;
    }

    /**
     * Determine whether the user can delete the schedule.
     */
    public function delete(User $user, Schedule $schedule): bool
    {
        //
        return $user->doctor && $user->doctor->id === $schedule->doctor_id;
    }

    /**
     * Determine whether the user can repeat the schedule.
     */
    public function repeat(User $user, Schedule $schedule): bool
    {
        //
        return $user->doctor !== null;
    }

}

