<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Specialization;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'specialization_id' => fake()->randomElement( Specialization::pluck('id') ),
            'bio'               => fake()->paragraph(),
            'phone'             => fake()->phoneNumber(),
            'bio'               => fake()->sentence(),
            'image'             => 'doctor.png',
        ];
    }
}
