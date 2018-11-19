<?php
/**
* 枚举类型主表
*/
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnumberatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enumberates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('枚举类型名称');
            $table->integer('user_id')->unsigned()->comment('添加人id');
            // $table->foreign('user_id')->references('id')->on('users');
            $table->softDeletes();
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
        Schema::dropIfExists('enumberates');
    }
}
