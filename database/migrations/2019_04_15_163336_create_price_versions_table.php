<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('price_versions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("category")->comment("分类");
            $table->integer("product_brand")->comment("品牌");
            $table->integer("date")->comment("更新日期");
            $table->string("version")->comment("版本号");
            $table->string("atta_id")->nullable()->comment("附件id集合");
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
        Schema::dropIfExists('price_versions');
    }
}