<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table)
        {

            $table->increments('id');

            $table->integer('role_id')->unsigned()->index()->nullable(); //modified
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');

            $table->enum('type', ['user', 'merchant'])->default('user');

            $table->string('first_name', 100);
            $table->string('last_name', 100);

            $table->string('formattedPhone', 30)->nullable()->default(null);

            $table->string('phone', 20)->unique()->nullable()->default(null);

            $table->text('google2fa_secret')->nullable(); //pm-1.5

            $table->string('defaultCountry', 4)->nullable()->default(null); //pm-1.3

            $table->string('carrierCode', 6)->nullable()->default(null); //pm-1.3

            $table->string('email')->unique();
            $table->string('password');
            $table->string('phrase')->nullable()->default(null);

            $table->boolean('address_verified')->default(false);
            $table->boolean('identity_verified')->default(false);

            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
