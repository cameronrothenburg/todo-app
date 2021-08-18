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
            $table->uuid('id')->primary();
            $table->boolean('sent');
            $table->dateTime('reminder_datetime');
            $table->foreignUuid('todo_item_id')->constrained('todo_items')->onDelete('cascade');
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
