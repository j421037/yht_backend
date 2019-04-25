<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("project", function(Blueprint $table) {
            $table->unsignedtinyInteger('created_month')->comment('创建月份');
			$table->unsignedInteger('created_year')->comment('创建年份');
			$table->string('brand')->comment('品牌');
			$table->unsignedtinyInteger('type')->default(0)->comment('类型');
			$table->string('addr')->comment('地址编码');
			$table->string('addr_detail')->comment('地址详情');
			$table->string('remark')->comment('项目概括');
			$table->unsignedinteger("start_at")->comment("开工日期");
            $table->unsignedinteger('finish_at')->comment('结束日期');
			$table->unsignedtinyInteger('status')->comment('项目状态');
			$table->string('area')->comment('建筑面积');
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
        //
    }
}
