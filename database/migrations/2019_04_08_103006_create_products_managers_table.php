<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('products_managers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("category_id")->unsigned()->comment("产品分类id");
            $table->string("brand_name")->comment("品牌名称");
            $table->integer("brand_id")->unsigned()->comment("品牌id");
            $table->string("table")->comment("对应价格表名称");
            $table->tinyInteger("method")->default(0)->comment("计算方式，默认[0]面价打折");
            $table->text("columns")->comment("字段信息, json:{name:外径,value:'DN'}");
            $table->foreign("category_id")->references("id")->on("product_categories");
            $table->foreign("brand_id")->references("id")->on("brands");
            $table->unique("table");
            $table->unique(["category_id","brand_id"]);
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
        Schema::dropIfExists('products_managers');
    }
}
