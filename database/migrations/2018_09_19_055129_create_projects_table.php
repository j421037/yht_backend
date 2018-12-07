<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('项目名称');
            $table->integer('cust_id')->unsigned()->comment('所属客户id');
            $table->integer('user_id')->unsigned()->comment('创建人id');
            $table->string('phone_num')->comment('联系电话');
            $table->integer('tag')->unsigned()->comment("客户标签,对应 enumerateItem表的ID");
            $table->integer("attachment_id")->unsigned()->comment("合同附件");
            $table->decimal('tax', 20, 2)->nullable()->comment('税点');
            $table->integer('tid')->unsigned()->comment('项目属性, 对应 enumerateItem表的ID');
            $table->string('payment_days')->nullable()->default(null)->comment('账期');
            $table->string('affiliate')->nullable()->comment('挂靠信息');
            $table->string('agreement')->nullable()->comment('合同信息');
            $table->string('estimate')->nullable()->comment('项目预计金额');
            // $table->foreign('cust_id')->references('id')->on('real_customers');
            // $table->foreign('user_id')->references('id')->on('users');
            // $table->foreign('attachment_id')->references('id')->on('attachments');
            $table->unique(['name','tid','tag']);
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
        Schema::dropIfExists('projects');
    }
}
