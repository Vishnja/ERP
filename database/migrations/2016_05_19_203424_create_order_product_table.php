<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_product', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('order_id')->unsigned();
            $table->foreign('order_id')->references('id')
                  ->on('orders')->onDelete('cascade');

            $table->integer('product_supplier_price_id')->unsigned();
            // no constraints on product_supplier_price deletion
            // because record should stay for archive purposes

            $table->string('product_name', 100);        // for archive purposes
            $table->decimal('price');                   // for archive purposes
            //$table->string('supplier_name', 100);       // for archive purposes

            $table->integer('quantity')->unsigned();

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
        Schema::drop('order_product');
    }
}
