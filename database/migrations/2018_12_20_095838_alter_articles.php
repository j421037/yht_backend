<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterArticles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("articles", function(Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->tinyInteger('module_id')->comment('文章所属模块');
            $table->string('attr')->comment('文章属性：public =>公开,protected => 部门内部可见');
            $table->text('abstract')->comment('文章摘要');
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
