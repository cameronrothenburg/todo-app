<?php

namespace Database\Factories;

use App\Models\TodoItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TodoItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'body' => $this->faker->paragraph(),
            'due_datetime' => $this->faker->dateTime(),
            'completed' => $this->faker->boolean(),
        ];
    }
}