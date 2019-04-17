<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeneralOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('general_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("creator_id")->comment("创建人");
            $table->string("creator")->comment("创建人");
            $table->integer("serviceor_id")->comment("业务员");
            $table->string("serviceor")->comment("业务员");
            $table->integer("customer_id")->comment("客户");
            $table->string("customer")->comment("客户");
            $table->tinyInteger("operate")->comment("操作");
            $table->decimal("operate_val")->comment("运算的值");
            $table->integer("product_brand_id")->comment("manager_id 对应的价格表");
            $table->integer("version_id")->comment("价格版本id");
            $table->text("products")->nullable()->comment("产品规格");
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
        Schema::dropIfExists('general_offers');
    }
}
