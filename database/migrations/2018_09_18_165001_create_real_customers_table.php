<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRealCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('real_customers');
        Schema::create('real_customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('status')->comment('客户状态');
            // $table->string('type')->comment('客户类型, 0 => 目标客户, 1 合作客户');
            $table->integer('user_id')->unsigned()->comment('创建人的id');
            //$table->foreign('user_id')->references('id')->on('users');
            $table->unique('name');
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
        Schema::dropIfExists('real_customers');
    }
}
