<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMakeOfferFormulasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('make_offer_formulas', function (Blueprint $table) {
            $table->increments('id');
            $table->string("formula")->comment("公式");
            $table->text("source")->comment("json");
            $table->text("formula_parse")->comment("解析后的公式");
            $table->string("label")->comment("公式显示的名称");
            $table->unsignedInteger("table_id")->comment("对应表名称");
            $table->unsignedInteger("user_id");
            $table->SoftDeletes();
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
        Schema::dropIfExists('make_offer_formulas');
    }
}
