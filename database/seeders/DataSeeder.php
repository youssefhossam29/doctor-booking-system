<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserType;

use App\Models\User;
use App\Models\Admin;
use App\Models\Specialization;
use App\Models\Doctor;
use App\Models\Schedule;
use App\Models\DoctorSlot;
use App\Models\Patient;
use App\Models\Appointment;

use Carbon\Carbon;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create 10 specializations
        $specializations = [
            'Cardiology', 'Dermatology', 'Neurology', 'Pediatrics', 'Orthopedics',
            'Psychiatry', 'Ophthalmology', 'Oncology', 'Endocrinology', 'Gastroenterology',
        ];

        foreach ($specializations as $spec) {
            Specialization::create(['name' => $spec]);
        }


        // create admin
        $adminUser = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'),
            'type'     => UserType::ADMIN,
        ]);

        Admin::create([
            'user_id' => $adminUser->id,
            'image'   => 'admin.png',
        ]);


        // create 5 doctor users, each with 3 schedules and slots
        User::factory(5)->state([
            'type' => UserType::DOCTOR,
        ])->create()->each(function ($user){
            $doctor = Doctor::factory()->create(['user_id' => $user->id]);

            $days = collect([
                'saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'
            ])->shuffle()->take(3);

            foreach ($days as $day) {
                $schedule = Schedule::factory()->create([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $day,
                ]);

                // Generate slots for the schedule
                $nextDate = Carbon::now()->next(strtolower($day))->format('Y-m-d');
                $start = Carbon::parse("{$nextDate} {$schedule->start_time}");
                $end = Carbon::parse("{$nextDate} {$schedule->end_time}");

                while ($start < $end) {
                    $slotEnd = $start->copy()->addMinutes($schedule->slot_duration);

                    DoctorSlot::firstOrCreate([
                        'doctor_id' => $doctor->id,
                        'date' => $nextDate,
                        'start_time' => $start->format('H:i:s'),
                    ], [
                        'end_time' => $slotEnd->format('H:i:s'),
                        'is_available' => 1,
                    ]);

                    $start = $slotEnd;
                }
            }
        });


        // create 5 patient users, foreach create 5 schedules
        User::factory(5)->state([
            'type' => UserType::PATIENT,
        ])->create()->each(function ($user){
            Patient::factory()->create(['user_id' => $user->id]);
        });


        Appointment::factory(30)->create();

    }
}
