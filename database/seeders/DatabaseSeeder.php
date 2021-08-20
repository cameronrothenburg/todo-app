<?php

namespace Database\Seeders;

use App\Models\TodoItem;
use App\Models\TodoNotification;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void {
        User::factory(1)->state(["email" => "user@example.com"])
            ->has(TodoItem::factory(10000)
                ->has(TodoNotification::factory(5))
            )
            ->create();
    }
}
