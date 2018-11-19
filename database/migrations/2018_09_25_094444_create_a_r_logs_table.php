<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateARLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('a_r_logs');
        Schema::create('a_r_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('操作人id');
            $table->integer('fid')->unsigned()->comment('操作目标id');
            $table->string('type')->comment('操作类型 增删改');
            $table->string('model')->comment('表模型');
            $table->string('old_value')->comment('原始值');
            $table->string('new_value')->comment('变动后的值');
            //$table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('a_r_logs');
    }
}
