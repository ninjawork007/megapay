<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table)
        {
            $table->increments('id');

            $table->integer('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('activator_id')->unsigned()->index()->nullable();
            $table->foreign('activator_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('currency_id')->unsigned()->index()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('cascade');

            $table->string('uuid', 13)->nullable()->comment = "Unique ID (For Each Voucher)";

            $table->double('charge_percentage', 10, 2)->nullable()->default(0.00);

            $table->double('charge_fixed', 10, 2)->nullable()->default(0.00);

            $table->double('amount', 10, 2)->nullable()->default(0.00);
            $table->string('code', 50)->nullable();

            $table->enum('redeemed', ['No','Yes'])->default('No'); //ALTER TABLE vouchers ADD redeemed enum('No','Yes') AFTER 'code'  -- for mysql query;

            $table->enum('status', ['Pending', 'Success','Refund','Blocked']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
}
