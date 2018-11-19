<?php
/**
* 枚举项 表
*/
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnumberateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enumberate_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('枚举项名称');
            $table->string('value')->comment('枚举项的值');
            $table->tinyInteger('index')->comment('枚举项的序号');
            $table->tinyInteger('disable')->default(0)->comment('是否禁用');
            $table->integer('eid')->unsigned()->comment('枚举名称');
            // $table->foreign('eid')->references('id')->on('enumberates');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name','eid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enumberate_items');
    }
}
