<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('customer_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedinteger('cust_id')->comment('客户id');
            $table->unsignedtinyInteger("machine")->comment("配套机器");
            $table->unsignedinteger('num')->comment('数量');
            $table->date("addtime")->comment("添加时间");
			$table->string("remark")->comment("备注");
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
        Schema::dropIfExists('customer_tags');
    }
}
