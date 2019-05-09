<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAReceivebills extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("a_receivebills", function (Blueprint $table) {
            $table->dropColumn("cust_id");
            $table->dropColumn("pid");
            $table->unsignedInteger("rid")->comment("对应的欠款表id");
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
