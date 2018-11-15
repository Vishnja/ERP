<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase;

class PurchasesTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();
        $faker = Faker\Factory::create();
        $type = ['receipt','realization'];


        foreach(range(1, 120) as $index)
        {
            $item = Purchase::create([
                'serial'                => str_pad($index, 8, "0", STR_PAD_LEFT),
                //'supplier_id'           => $faker->numberBetween(1, 100),
                'type'                  => $type[$faker->numberBetween(0, 1)],
                // 'total' is calculated later
                'status_id'             => $faker->numberBetween(1, 3),
                'paid'                  => $faker->boolean,
                'shipped'               => $faker->boolean,
                'created_at'            => $datetime = $faker->dateTimeBetween('-1 years', 'now'),
                'updated_at'            => $datetime
            ]);

            $productsNum = $faker->numberBetween(1, 10);

            $total = 0;
            foreach(range(1, $productsNum) as $index2) {

                $productSupplierPriceId = $faker->numberBetween(1, 100);
                $productSupplierPrice =
                    \App\Models\ProductSupplierPrice::find( $productSupplierPriceId );

                $purchasePrice = $productSupplierPrice->purchase_price;

                $quantity = $faker->numberBetween(1, 10);
                $item->products()->attach( $productSupplierPriceId, [
                    'product_name'      => $productSupplierPrice->product->name,
                    'purchase_price'    => $purchasePrice,
                    'supplier_name'     => $productSupplierPrice->supplier->name,
                    'quantity'          => $quantity,
                ] );

                $total += $purchasePrice * $quantity;
            }

            // total price
            $item->total = $total;
            $item->save();
        }
    }

}