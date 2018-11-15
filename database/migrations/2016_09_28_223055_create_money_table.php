<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMoneyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('money', function (Blueprint $table) {
            $table->increments('id');

            $table->string('serial', 50);
            $table->enum('record_type', ['operational', 'management'])->default('operational');
            $table->enum('money_type', ['cash', 'account'])->default('account');
            $table->integer('income_expense_item_id')->unsigned();

            $table->integer('contractor_id')->unsigned()->nullable(); // buyer or supplier
            $table->string('contractor_type', 100)->nullable();

            $table->integer('base_id')->unsigned()->nullable(); // order or purchase
            $table->string('base_type', 100)->nullable();

            $table->text('comment')->nullable();
            $table->decimal('total');
            $table->enum('status', ['active', 'cancelled'])->default('active');
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
        Schema::drop('money');
    }
}
