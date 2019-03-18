<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('offers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('create_user_id')->comment("创建人");
            $table->string("project")->nullable()->comment("项目名称");
            $table->string("linkman")->nullable()->comment("联系人");
            $table->string("tel")->nullable()->comment("联系方式");
            $table->integer('user_id')->comment("业务员");
            $table->tinyInteger("bid")->nullable()->comment("品牌id");
            $table->string('unload')->nullable()->comment("装卸");
            $table->string("dispatch")->nullable()->comment("配送");
            $table->string("tax")->nullable()->comment("税率");
            $table->string("date")->nullable()->comment("报价日期");
            $table->text("description")->nullable()->comment("备注");
            $table->text("migrate_id")->nullable()->comment("迁移ID");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offers');
    }
}
