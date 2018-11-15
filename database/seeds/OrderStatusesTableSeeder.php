<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderStatus;

class OrderStatusesTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();

        OrderStatus::create(['name' => 'Открытый']);
        OrderStatus::create(['name' => 'Отменен']);
        OrderStatus::create(['name' => 'Выполнен']);
    }

}