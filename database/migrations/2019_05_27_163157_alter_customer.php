<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table("customers", function(Blueprint $table) {
            $table->unsignedInteger("real_customer_id")->nullable()->comment("对应的真实客户");
            $table->unsignedInteger("real_project_id")->nullable()->comment("项目id");
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
