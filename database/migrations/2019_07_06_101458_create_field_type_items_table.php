<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFieldTypeItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('field_type_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("table_id")->comment("表id，productMananger");
            $table->string("field")->comment("字段名称");
            $table->string("key")->comment("列表项名称");
            $table->string("value")->comment("列表项的值");
            $table->unsignedInteger("user_id")->comment("创建人");
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
        Schema::dropIfExists('field_type_items');
    }
}
