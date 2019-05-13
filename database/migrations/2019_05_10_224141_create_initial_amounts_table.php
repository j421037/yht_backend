<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInitialAmountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('initial_amounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("rid");
            $table->unsignedDecimal("amountfor",23,3)->comment("期初金额");
            $table->unsignedInteger("date")->comment("日期");
            $table->unsignedTinyInteger("type")->default(0)->comment("终端or同行");
            $table->text("remark")->nullable()->comment("备注");
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
        Schema::dropIfExists('initial_amounts');
    }
}
