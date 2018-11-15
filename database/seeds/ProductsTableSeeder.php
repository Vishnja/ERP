<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class ProductsTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();
        $faker = Faker\Factory::create();

        foreach(range(1, 100) as $index)
        {
            Product::create([
                'name'                  => $faker->text(100),
                'vendor_code'           => $faker->uuid,
                'description'           => $faker->text,
                'price'                 => $faker->randomFloat(2, 5, 1000),
                'quantity_receipt'      => $faker->numberBetween(0, 1000),
                'quantity_realization'  => $faker->numberBetween(0, 1000),
            ]);
        }
    }

}