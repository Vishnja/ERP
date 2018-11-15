<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');

            $table->string('serial', 50);
            $table->integer('buyer_id')->unsigned();
            $table->string('buyer_fullname', 100);
            $table->integer('payment_method_id')->unsigned();
            $table->integer('shipping_method_id')->unsigned();
            $table->string('NP_city_store', 100)->nullable();
            $table->decimal('shipping_cost')->default('0');

            $table->enum('discount_type', ['currency', 'percent'])->default('currency');
            $table->decimal('discount_value')->nullable();

            $table->decimal('grand_total');

            $table->boolean('paid')->default(false);
            $table->boolean('shipped')->default(false);
            $table->integer('status_id')->unsigned();

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
        Schema::drop('orders');
    }
}
