<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('article_datas');
        Schema::create('article_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('article_id')->unsigned()->comment('对应的文章id');
            $table->integer('clicks')->default(0)->comment('点击量');//
            $table->integer('comments')->default(0)->comment('评论数量');
            $table->integer('agrees')->default(0)->comment('点赞数量');
            $table->integer('last_comment_user_id')->unsigned()->nullable()->comment('最后评论人');
            $table->foreign('last_comment_user_id')->references('id')->on('users');
            $table->foreign('article_id')->references('id')->on('articles');
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
        Schema::dropIfExists('article_datas');
    }
}
