<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('forum_modules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('module_name')->comment('版块名称');
            $table->boolean('disable')->default(0)->comment('禁用状态');
            $table->timestamps();
            $table->softDeletes();
            //$table->unique('module_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forum_modules');
    }
}
