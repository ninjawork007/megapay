<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('short_name', 5);
            $table->string('flag', 100)->nullable();
            $table->enum('default', ['1', '0']);
            $table->enum('deletable', ['Yes', 'No'])->default('Yes'); //ALTER TABLE languages ADD deletable enum('Yes','No') AFTER 'default'  -- for mysql query;
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('languages');
    }
}
