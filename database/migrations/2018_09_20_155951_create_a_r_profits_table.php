<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateARProfitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('a_r_profits');
        // Schema::create('a_r_profits', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->integer('cust_id')->unsigned()->comment('客户ID');
        //     $table->decimal('percent', 10,2)->comment('利润点');
        //     $table->integer('year')->unsigned()->comment('年');
        //     $table->integer('month')->unsigned()->comment('月');
        //     $table->foreign('cust_id')->references('id')->on('real_customers');
        //     $table->unique('year','month');
        //     $table->timestamps();
        //     $table->softDeletes();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('a_r_profits');
    }
}
