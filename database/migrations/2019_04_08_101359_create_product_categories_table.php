<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('product_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name")->comment("产品分类名称");
            $table->integer("creator")->comment("创建人");
            $table->string("abbr")->comment("名称缩写");
            $table->timestamps();
            $table->unique(['name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
}
