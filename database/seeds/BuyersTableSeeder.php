<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Buyer;

class BuyersTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();
        $faker = Faker\Factory::create();
        $genderArr = ['male', 'female'];

        foreach(range(1, 100) as $index)
        {
            $gender = $genderArr[$faker->numberBetween(0, 1)];
            Buyer::create([
                'name'      => $faker->firstName($gender),
                'surname'   => $faker->lastName($gender),
                'phone'     => $faker->phoneNumber,
                'email'     => $faker->email,
                'city'      => $faker->city,
                'address'   => $faker->streetAddress,
                'NP_number' => $faker->numberBetween(1, 20),
            ]);
        }
    }

}