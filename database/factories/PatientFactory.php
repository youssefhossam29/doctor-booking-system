<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image'         => 'patient.png',
            'date_of_birth' => fake()->date('Y-m-d'),
            'gender'        => fake()->boolean(),
            'phone'         => fake()->phoneNumber(),
        ];
    }
}
