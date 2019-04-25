<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndexStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('index_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('target', 20, 2)->nullable()->comment('年度目标业绩');
            $table->decimal('completed', 20, 2)->nullable()->comment('已完成业绩');
            $table->unsignedinteger('user_id')->comment('用户id');
            $table->decimal('debt', 20, 2)->nullable()->comment('欠款');
            $table->decimal('debt_percent', 20, 2)->nullable()->comment('欠款比');
            $table->unsignedinteger("target_client")->comment("目标客户");
            $table->unsignedinteger('report_client')->comment('报备客户');
            $table->unsignedinteger("coop_client")->comment("合作客户");
			$table->unsignedinteger("lose_client")->comment("流失客户");
			$table->unsignedinteger("brand_price")->comment("品牌报价");
			$table->unsignedinteger("rt_price")->comment("即时报价");
			$table->unsignedinteger("other_price")->comment("其他报价");
			$table->unsignedinteger("machine")->comment("配置机器");
			$table->unsignedinteger("censor")->comment("累计送检");
			$table->unsignedinteger("mynote")->comment("我的帖子");
			$table->unsignedinteger("likes")->comment("我的赞数");
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
        Schema::dropIfExists('index_statistics');
    }
}
