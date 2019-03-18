<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        Schema::create('offer_attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('path')->comment('文件存放路径');
            $table->string('name')->comment('文件名');
            $table->integer('cid')->comment('对应的报价id');
            $table->string('size')->comment('附件大小');
            $table->string('mimitype')->comment('附件类型');
            $table->text("migrate_id")->nullable()->comment("迁移ID");
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
        Schema::dropIfExists('offer_attachments');
    }
}
