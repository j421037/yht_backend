<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table("permissions", function(Blueprint $table) {
            $table->unsignedInteger('show_pc')->default(0)->comment('是否在PC端显示,1显示默认0');
            $table->unsignedInteger('show_mobile')->default(0)->comment('是否在移动端显示,1显示默认0');
            $table->longText('template_pc')->nullable()->comment('pc端模板');
            $table->longText('template_mobile')->nullable()->comment('移动端模板');
            $table->string('mobile_name')->nullable()->comment('移动端名称');
            $table->string('mobile_path')->nullable()->comment('移动端路由');
            $table->string('template_pc_name')->nullable()->comment('PC端模板名称');
            $table->string('template_mobile_name')->nullable()->comment('移动端模板名称');
            $table->string('mobile_classname')->nullable()->comment('移动端类名');
            $table->unsignedInteger('mobile_sort')->nullable()->default(0)->comment('移动端排序');
            $table->unsignedInteger('pc_sort')->nullable()->default(0)->comment('PC端类名');
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
