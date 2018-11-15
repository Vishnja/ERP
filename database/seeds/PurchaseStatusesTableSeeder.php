<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\PurchaseStatus;

class PurchaseStatusesTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();

        PurchaseStatus::create(['name' => 'Открытый']);
        PurchaseStatus::create(['name' => 'Отменен']);
        PurchaseStatus::create(['name' => 'Выполнен']);
    }

}