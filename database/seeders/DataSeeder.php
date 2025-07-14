<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserType;

use App\Models\User;
use App\Models\Admin;
use App\Models\Specialization;
use App\Models\Doctor;
use App\Models\Schedule;
use App\Models\Patient;
use App\Models\Appointment;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create 10 specializations
         $specializations = [
            'Cardiology',
            'Dermatology',
            'Neurology',
            'Pediatrics',
            'Orthopedics',
            'Psychiatry',
            'Ophthalmology',
            'Oncology',
            'Endocrinology',
            'Gastroenterology',
        ];

        foreach ($specializations as $spec) {
            Specialization::create([
                'name' => $spec,
            ]);
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


        // create 5 doctor users, foreach create 5 schedules
        User::factory(5)->state([
            'type' => UserType::DOCTOR,
        ])->create()->each(function ($user){
            $doctor = Doctor::factory()->create(['user_id' => $user->id]);
            Schedule::factory(5)->create(['doctor_id' => $doctor->id]);
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
