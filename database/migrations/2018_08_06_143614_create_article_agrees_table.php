<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleAgreesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('article_agrees');
        Schema::create('article_agrees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('article_id')->unsigned()->comment('对应的文章id');
            $table->integer('create_user_id')->unsigned()->comment('文章创建人的id');
            $table->integer('agree_user_id')->unsigned()->comment('点赞人的id');
            $table->foreign('article_id')->references('id')->on('articles');
            $table->foreign('create_user_id')->references('id')->on('users');
            $table->foreign('agree_user_id')->references('id')->on('users');
            $table->timestamps();
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
        Schema::dropIfExists('article_agrees');
    }
}
