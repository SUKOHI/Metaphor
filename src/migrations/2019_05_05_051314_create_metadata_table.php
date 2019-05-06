<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetadataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metadata', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('parent');
            $table->integer('parent_id')->unsigned();
            $table->string('type')->default('null');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->index(['parent', 'parent_id']);
            $table->unique(['parent', 'parent_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('metadata');
    }
}
