<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Doctor;
use App\Models\Patient;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'doctor_id'         => fake()->randomElement( Doctor::pluck('id') ),
            'patient_id'        => fake()->randomElement( Patient::pluck('id') ),
            'appointment_date'  => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'appointment_time'  => fake()->time('H:i'),
            'status'            => fake()->randomElement(['pending', 'cancelled']),
            'notes'             => fake()->optional()->sentence(),
        ];
    }
}
