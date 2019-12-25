<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_types', function (Blueprint $table)
        {
            $table->increments('id');
            $table->enum('name', [
                'Deposit',
                'Withdrawal',
                'Transferred',
                'Received',
                'Exchange_From',
                'Exchange_To',
                'Voucher_Created',
                'Voucher_Activated',
                'Request_From',
                'Request_To',
                'Payment_Sent',
                'Payment_Received',
            ]);

            // ALTER TABLE `transaction_types` CHANGE `name` `name` ENUM('Deposit',
            //     'Withdrawal',
            //     'Transferred',
            //     'Received',
            //     'Exchange_From',
            //     'Exchange_To',
            //     'Voucher_Created',
            //     'Voucher_Activated',
            //     'Request_From',
            //     'Request_To',
            //     'Payment_Sent',
            //     'Payment_Received','Bank_Transfer') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_types');
    }
}
