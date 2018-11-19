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
        //
        Schema::dropIfExists('articles');
        Schema::create('articles', function(Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->longText('body');
            $table->integer('category_id')->unsigned()->comment('文章分类');
            $table->tinyInteger('status')->default(0)->comment('文章的状态: 暂存=> 0, 发布=> 1'); //是否已发布
            $table->tinyInteger('top')->default(0)->comment('置顶: 不置顶 => 0, 置顶 => 1');
            $table->integer('user_id')->unsigned()->comment('创建人');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('article_categories');
            $table->timestamps();
            $table->text('titlepic')->nullable()->comment('文章缩略图');
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
