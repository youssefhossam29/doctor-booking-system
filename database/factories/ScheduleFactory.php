<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use DateTime;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $times = ['08:00:00', '09:00:00', '10:00:00', '11:00:00', '12:00:00', '13:00:00', '14:00:00',
                '15:00:00', '16:00:00', '17:00:00', '18:00:00', '19:00:00', '20:00:00' ];

        $start_time = fake()->unique()->randomElement($times);
        $startDateTime = new DateTime($start_time);
        $end_time = $startDateTime->modify('+60 minutes')->format('H:i:s');

        return [
            'day_of_week' => fake()->randomElement([
                'saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'
            ]),
            'start_time' => $start_time,
            'end_time' =>  $end_time,
            'slot_duration' => '15'
        ];
    }

}
