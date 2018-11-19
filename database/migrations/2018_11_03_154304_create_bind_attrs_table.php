<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBindAttrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('bind_attrs');
        Schema::create('bind_attrs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('实体名称');
            $table->string('key')->comment('属性标识');
            $table->integer('pid')->unsigned()->comment('功能对应id');
            $table->integer('eid')->unsigned()->comment();
            // $table->foreign('pid')->references('id')->on('permissions');
            // $table->foreign('eid')->references('id')->on('enumberates');
            $table->timestamps();
            $table->softDeletes();
            $table->unique('key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bind_attrs');
    }
}
