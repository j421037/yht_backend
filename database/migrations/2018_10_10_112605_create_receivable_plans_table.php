<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceivablePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receivable_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pid')->unsigned()->comment('项目id');
            $table->integer('user_id')->unsigned()->comment('添加人id');
            $table->tinyInteger('week')->unsigned()->comment('第几周');
            $table->text('content')->comment('计划内容');
            $table->string('date')->comment('计划日期');
            //$table->foreign('pid')->references('id')->on('projects');
            //$table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receivable_plans');
    }
}
