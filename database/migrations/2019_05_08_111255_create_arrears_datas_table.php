<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArrearsDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('arrears_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->string("customer_name")->comment("客户名称");
            $table->unsignedInteger("customer_id")->comment("客户id");
            $table->string("project_name")->comment("项目名称");
            $table->unsignedInteger("project_id")->comment("项目id");
            $table->unsignedTinyInteger("tag")->default(0)->comment("标签");
            $table->unsignedTinyInteger("status")->default(0)->comment("状态");
            $table->unsignedInteger("contract")->default(0)->comment("合同");
            $table->unsignedInteger("work_scope")->default(0)->comment("施工范围");
            $table->string("work_scope_name")->nullable()->comment("施工范围名称");
            $table->unsignedTinyInteger("attached")->default(0)->comment("挂靠");
            $table->string("user_name")->comment("业务员");
            $table->unsignedInteger("user_id")->comment("业务员ID");
            $table->string("tax")->nullable()->comment("税率");
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
        Schema::dropIfExists('arrears_datas');
    }
}
