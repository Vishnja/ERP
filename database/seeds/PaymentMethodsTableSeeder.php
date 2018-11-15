<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentMethod;

class PaymentMethodsTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();

        PaymentMethod::create([
            'name' => 'Наличные',
        ]);

        PaymentMethod::create([
            'name' => 'Банковской картой',
        ]);

        PaymentMethod::create([
            'name' => 'Б/Н',
        ]);
    }

}