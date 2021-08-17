<?php

namespace Database\Factories;

use App\Models\TodoNotification;
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
    public function definition()
    {
        return [
            'reminder_datetime' => $this->faker->dateTime(),
        ];
    }
}
