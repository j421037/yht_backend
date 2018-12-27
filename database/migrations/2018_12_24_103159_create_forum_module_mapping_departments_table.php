<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumModuleMappingDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('forum_module_mapping_departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('映射名称');
            $table->integer('sid')->comment('对应的部门id或者自定义模块id');
            $table->string('model')->comment('对应的模型');
            $table->string('attr')->comment('属性 public或者protected');
            $table->integer('index')->comment('排序');
            $table->timestamps();
            $table->softDeletes();
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forum_module_mapping_departments');
    }
}
