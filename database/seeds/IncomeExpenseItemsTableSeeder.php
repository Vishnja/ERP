<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\IncomeExpenseItem;

class IncomeExpenseItemsTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();

        for ($i=1; $i<=25; $i++) {
            IncomeExpenseItem::create(['name' => "Прих. $i", 'type' => 'income']);
            IncomeExpenseItem::create(['name' => "Расх. $i", 'type' => 'expense']);
        }

    }

}