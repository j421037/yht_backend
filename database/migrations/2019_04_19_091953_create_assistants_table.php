<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssistantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('assistants', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger("department_id")->comment("部门id");
            $table->unsignedTinyInteger("user_id")->comment("用户id");
            $table->string("department")->comment("部门名称");
            $table->string("name")->comment("用户名");
            $table->unique(["department_id","user_id"]);
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
        Schema::dropIfExists('assistants');
    }
}
