<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterArticleComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table("article_comments", function(Blueprint $table) {
            $table->integer('answer_id')->unsigned()->comment('该条评论对应answer表的id');
            $table->integer('to_user_id')->unsigned()->nullable()->comment('被回复人');
            $table->foreign('answer_id')->references('id')->on('article_answers');
            $table->foreign('to_user_id')->references('id')->on('users');
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
