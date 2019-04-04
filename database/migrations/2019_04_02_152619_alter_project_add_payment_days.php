<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProjectAddPaymentDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("projects", function(Blueprint $table) {
            $table->integer("payment_days")->default(0)->comment("账期,天数");
            $table->integer("payment_start_date")->comment("开始计算付款账期的日期节点");
            $table->integer("last_payment_date")->default(0)->comment("最后付款日期");
            $table->boolean("isclose")->default(0)->comment("项目是否关闭");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
