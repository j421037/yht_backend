<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerTracksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('customer_tracks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cust_id')->comment('客户id');
            $table->string("content")->comment("内容");
            $table->date("addtime")->comment("添加时间");
            $table->softDeletes();
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
        Schema::dropIfExists('customer_tracks');
    }
}
