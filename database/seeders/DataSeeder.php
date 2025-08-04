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
use App\Models\Contact;
use App\Models\ContactReply;

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

        // Create patients
        User::factory(10)->state([
            'type' => UserType::PATIENT,
        ])->create()->each(function ($user) {
            Patient::factory()->create(['user_id' => $user->id]);
        });

        $patients = Patient::pluck('id');

        // Create doctors + schedules + slots + appointments
        User::factory(5)->state([
            'type' => UserType::DOCTOR,
        ])->create()->each(function ($user) use ($patients) {
            $doctor = Doctor::factory()->create(['user_id' => $user->id]);

            $days = collect([
                'saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'
            ])->shuffle()->take(2);

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

            // Get 4 available slots for this doctor
            $availableSlots = DoctorSlot::where('doctor_id', $doctor->id)
                ->where('is_available', 1)
                ->inRandomOrder()
                ->limit(4)
                ->get();

            foreach ($availableSlots as $slot) {
                $patientId = $patients->random();

                // Create appointment
                Appointment::create([
                    'doctor_id'        => $doctor->id,
                    'patient_id'       => $patientId,
                    'appointment_date' => $slot->date,
                    'appointment_time' => $slot->start_time,
                    'status'           => 'pending',
                    'notes'            => fake()->optional()->sentence(),
                ]);

                // Mark slot as unavailable
                $slot->update(['is_available' => 0]);
            }
        });

        // Generate Contacts + Replies
        User::whereIn('type', [UserType::DOCTOR, UserType::PATIENT])->get()->each(function ($user) use ($adminUser) {

            // Generate two Contacts foreach user
            Contact::factory(2)->create([
                'user_id'   => $user->id,
                'user_type' => $user->type,
            ])->each(function ($contact) use ($adminUser) {

                // Generate reply from admin foreach contact
                ContactReply::factory()->create([
                    'contact_id' => $contact->id,
                    'user_id'    => $adminUser->id,
                    'user_type'  => $adminUser->type,
                ]);
            });
        });
    }
}
