<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\Buyer;

class OrdersTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();
        $faker = Faker\Factory::create();
        $prefix = ['SHP-', 'ERP-'];
        $discount_type = ['currency','percent'];

        foreach(range(1, 120) as $index)
        {
            $buyer = Buyer::find($faker->numberBetween(1, 100));

            $item = Order::create([
                'serial'                => $prefix[$faker->numberBetween(0, 1)] .
                                           str_pad($index, 8, "0", STR_PAD_LEFT),
                'buyer_id'              => $buyer->id,
                'buyer_fullname'        => $buyer->fullname,
                'payment_method_id'     => $faker->numberBetween(1, 3),
                'shipping_method_id'    => $faker->numberBetween(1, 3),
                'status_id'             => $faker->numberBetween(1, 3),
                'discount_value'        => $faker->randomFloat(2, 0, 100),
                'discount_type'         => $discount_type[$faker->numberBetween(0, 1)],
                'paid'                  => $faker->boolean,
                'shipped'               => $faker->boolean,
                'created_at'            => $datetime = $faker->dateTimeBetween('-1 years', 'now'),
                'updated_at'            => $datetime
            ]);

            $productsNum = $faker->numberBetween(1, 10);
            $total = 0;
            foreach(range(1, $productsNum) as $index2) {
                $productSupplierPriceId = $faker->numberBetween(1, 100);
                $productSupplierPrice = \App\Models\ProductSupplierPrice::find( $productSupplierPriceId );
                $product = $productSupplierPrice->product;
                $productPrice = $product->price;

                // price changes in 20% of cases
                /*
                if ($faker->numberBetween(1, 100) > 80) {
                    $percentage = 5 - $faker->numberBetween(1, 10);
                    $productPrice = $productPrice + $productPrice * $percentage / 100;
                }
                */

                $quantity = $faker->numberBetween(1, 10);
                $item->products()->attach( $productSupplierPriceId, [
                    'product_name' => $product->name,
                    'price' => $productPrice,
                    //'supplier_name' => $productSupplierPrice->supplier->name,
                    'quantity' => $quantity,
                ] );

                $total += $productPrice * $quantity;
            }

            // shipping and NP_city_store
            switch ($item->shipping_method_id) {
                // courier
                case '1':
                    if ($total <= 350) $shipping_cost = 30;
                    break;
                // Nova Poshta
                case '3':
                    // todo: get price from api ?

                    // NP city store
                    $item->NP_city_store = $faker->city . ', ' . $faker->numberBetween(1, 50);

                    // set shipping cost
                    $shipping_cost = 20;
                    break;
                default:
                    $shipping_cost = 0;
            }

            // total price and shipping cost
            $item->shipping_cost = $shipping_cost;
            $item->grand_total = $item->discount_type == 'currency' ?
                                 $total - $item->discount_value + $shipping_cost :
                                 $total - $total * $item->discount_value / 100 + $shipping_cost;

            $item->save();
        }
    }

}