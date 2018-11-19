<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAReceivablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('a_receivables');
        Schema::create('a_receivables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cust_id')->unsigned()->comment('客户id');
            $table->integer('pid')->unsigned()->comment('对应的项目id');
            $table->decimal('amountfor', 23,3)->comment('本次收款金额');
            $table->integer('date')->unsigned()->nullable()->comment('业务日期');
            $table->tinyInteger('is_init')->unsigned()->comment('是否期初应收');
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
        Schema::dropIfExists('a_receivables');
    }
}
