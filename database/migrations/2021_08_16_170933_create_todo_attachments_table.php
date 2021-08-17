<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTodoAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todo_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('storage_type');
            $table->string('file_type')->nullable();
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
        Schema::dropIfExists('todo_attachments');
    }
}
