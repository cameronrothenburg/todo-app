<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTodoNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todo_notifications', function (Blueprint $table) {
            $table->id();
            $table->boolean('sent');
            $table->dateTime('reminder_datetime');
            $table->foreignUuid('todo_item_id')->constrained('todo_items');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('todo_notifications');
    }
}
