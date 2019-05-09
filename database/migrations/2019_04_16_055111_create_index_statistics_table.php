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
            $table->unsignedinteger("target_client")->default(0)->comment("目标客户");
            $table->unsignedinteger('report_client')->default(0)->comment('报备客户');
            $table->unsignedinteger("coop_client")->default(0)->comment("合作客户");
			$table->unsignedinteger("lose_client")->default(0)->comment("流失客户");
			$table->unsignedinteger("brand_price")->default(0)->comment("品牌报价");
			$table->unsignedinteger("rt_price")->default(0)->comment("即时报价");
			$table->unsignedinteger("other_price")->default(0)->comment("其他报价");
			$table->unsignedinteger("machine")->default(0)->comment("配置机器");
			$table->unsignedinteger("censor")->default(0)->comment("累计送检");
			$table->unsignedinteger("mynote")->default(0)->comment("我的帖子");
			$table->unsignedinteger("likes")->default(0)->comment("我的赞数");
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
