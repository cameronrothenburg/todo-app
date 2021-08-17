<?php

namespace Database\Seeders;

use App\Models\TodoItem;
use App\Models\TodoNotification;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
        User::factory(10)
            ->has(
                TodoItem::factory()->count(100)
                ->has(TodoNotification::factory()->count(4))
            )
            ->create();
    }
}
