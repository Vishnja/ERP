<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\ShippingMethod;

class ShippingMethodsTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();

        ShippingMethod::create([
            'name' => 'Курьером по Киеву',
        ]);

        ShippingMethod::create([
            'name' => 'Самовывоз',
        ]);

        ShippingMethod::create([
            'name' => 'Новая почта',
        ]);
    }

}