<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('refunds');
        Schema::create('refunds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cust_id')->unsigned()->comment('客户id');
            $table->integer('pid')->unsigned()->comment('对应的项目id');
            $table->decimal('refund', 23,3)->comment('本次退款金额');
            $table->integer('date')->unsigned()->nullable()->comment('日期');
            $table->text('remark')->nullable()->comment('备注');
            //$table->foreign('pid')->references('id')->on('projects');
            //$table->foreign('cust_id')->references('id')->on('real_customers');
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
        Schema::dropIfExists('refunds');
    }
}
