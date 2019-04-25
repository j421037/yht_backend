<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('customer_records', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cust_id')->comment('客户id');
            $table->unsignedTinyInteger("groups")->comment("组");
            $table->unsignedInteger('num')->comment('数量');
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
        Schema::dropIfExists('customer_records');
    }
}
