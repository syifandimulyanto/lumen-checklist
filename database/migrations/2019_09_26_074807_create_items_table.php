<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->nullable();
            $table->integer('checklist_id');
            $table->integer('assignee_id')->nullable();
            $table->integer('task_id');
            $table->string('description');
            $table->boolean('is_completed')->default(false);
            $table->dateTime('due')->nullable();
            $table->smallInteger('urgency')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->string('last_update_by')->nullable();
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
        Schema::dropIfExists('items');
    }
}
