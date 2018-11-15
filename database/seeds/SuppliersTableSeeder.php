<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;

class SuppliersTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();
        $faker = Faker\Factory::create();

        foreach(range(1, 100) as $index)
        {
            Supplier::create([
                'name'              => $faker->company,
                'phone'             => $faker->phoneNumber,
                'email'             => $faker->email,
                'contact_person'    => $faker->firstName . ' ' . $faker->lastName
            ]);
        }
    }

}