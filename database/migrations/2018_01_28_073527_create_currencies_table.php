<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('name', 100)->nullable()->default('USD');
            $table->char('symbol', 50)->default('$');
            $table->string('code', 100)->nullable()->default('101');
            $table->string('hundreds_name', 100)->nullable()->default('one thousand');
            $table->double('rate');
            $table->string('logo', 100)->nullable();
            $table->enum('default', ['1','0']);
            $table->enum('exchange_from', ['local','api'])->default('local');
            $table->enum('status', ['Active','Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}
