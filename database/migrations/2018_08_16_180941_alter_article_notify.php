<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterArticleNotify extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_notifies', function (Blueprint $table) {
            $table->integer('article_id')->unsigned()->comment('文章id');
            $table->integer('sender')->unsigned()->comment('发送人');
            $table->integer('receiver')->unsigned()->comment('接收人');
            $table->tinyInteger('is_read')->default(0)->comment('是否已读 0 1');
            $table->integer('answer_id')->unsigned()->nullable()->comment('回答对应的id');

            $table->foreign('article_id')->references('id')->on('articles');
            $table->foreign('sender')->references('id')->on('users');
            $table->foreign('receiver')->references('id')->on('users');
            $table->foreign('answer_id')->references('id')->on('article_answers');
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
