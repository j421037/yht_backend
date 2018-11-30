<?php
/**
*潜在项目表
 */
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePotentialProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('potential_projects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsingned()->comment('创建人id');
            $table->integer('cust_id')->unsigned()->comment('项目id');
            $table->string('phone_num')->nullable()->comment('联系方式');
            $table->string('name')->comment('项目名');
            $table->string('tag')->nullable()->comment('客户标签');
            $table->string('tid')->nullable()->commnet('项目属性');
            $table->string('estimate')->nullable()->comment('预计金额');
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
        Schema::dropIfExists('potential_projects');
    }
}
