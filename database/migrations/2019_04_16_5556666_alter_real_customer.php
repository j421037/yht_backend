<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRealCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::dropIfExists('real_customer');
        Schema::create('real_customer', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('user_id')->comment('创建人');
			$table->unsignedInteger('cust_id')->comment('客户id');
			$table->string('phone_num')->comment('手机号');
            $table->unsignedInteger('pid')->comment('项目id');
            $table->tinyInteger('type')->default(0)->comment('项目类型');
            
            
            $table->unsignedTinyInteger('work_scope')->comment('施工范围');
			//$table->tinyInteger('project_type')->unsigned()->comment('类型');
			$table->unsignedTinyInteger('attached')->comment('挂靠');
			$table->unsignedInteger('tags')->comment('标签');
            $table->unsignedInteger('contract')->comment('合同');
			$table->unsignedInteger('account_period')->comment('账期');
            $table->unsignedInteger('track')->comment('动态跟踪');
			$table->string('tax')->comment('税率');
			$table->unsignedInteger('coop')->comment('合作产品');
			$table->unsignedTinyInteger('level')->comment('级别');
            $table->timestamps();
            $table->text('titlepic')->nullable()->comment('文章缩略图');
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
        //
    }
}
