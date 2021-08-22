<?php

namespace Database\Factories;

use App\Models\TodoNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoNotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TodoNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array {
        return [
            'reminder_datetime' => Carbon::now()->addDays($this->faker->numberBetween(0,29)),
        ];
    }
}
