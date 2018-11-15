<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductSupplierPrice;

class ProductSupplierPriceTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();
        $faker = Faker\Factory::create();

        foreach(range(1, 100) as $index)
        {
            $productId = $faker->numberBetween(1, 100);
            $productPrice = \App\Models\Product::find( $productId )->price;

            // price should be less than actual (from 0 to 5%)
            $percentage = 5 - $faker->numberBetween(0, 5);
            $productPrice = $productPrice - $productPrice * $percentage / 100;

            ProductSupplierPrice::create([
                'product_id'        => $productId,
                'supplier_id'       => $faker->numberBetween(1, 10),
                'purchase_price'    => $productPrice
            ]);
        }
    }

}