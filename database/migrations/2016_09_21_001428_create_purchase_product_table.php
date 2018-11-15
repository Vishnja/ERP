<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_product', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('purchase_id')->unsigned();
            $table->foreign('purchase_id')->references('id')
                  ->on('purchases')->onDelete('cascade');

            $table->integer('product_supplier_price_id')->unsigned();
            // no constraints on product_supplier_price deletion
            // because record should stay for archive purposes

            $table->string('product_name', 100);        // for archive purposes
            $table->decimal('purchase_price');          // for archive purposes
            $table->string('supplier_name', 100);       // for archive purposes

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
        Schema::drop('purchase_product');
    }
}
