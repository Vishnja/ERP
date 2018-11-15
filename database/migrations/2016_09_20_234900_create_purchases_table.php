<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->increments('id');

            $table->string('serial', 50);
            //$table->integer('supplier_id')->unsigned();
            $table->enum('type', ['receipt', 'realization'])->default('receipt');
            $table->decimal('total');
            $table->integer('status_id')->unsigned();

            $table->boolean('paid')->default(false);
            $table->boolean('shipped')->default(false);

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
        Schema::drop('purchases');
    }
}
