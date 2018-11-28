<?php
/**
 *过滤  用户配置
 */
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilterProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        self::down();
        Schema::create('filter_programs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('过滤方案名称');
            $table->integer('user_id')->unsigned()->comment('用户ID');
            $table->string('module')->comment('过滤方案所属模块');
            $table->longText('conf')->comment('配置信息 已json的格式存放');
            $table->boolean('default')->default(false)->comment('是否默认进入');
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
        Schema::dropIfExists('filter_programs');
    }
}
