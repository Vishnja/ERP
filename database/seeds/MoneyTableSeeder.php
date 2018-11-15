<?php
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Money;

class MoneyTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();
        $faker = Faker\Factory::create();
        $record_types = ['operational', 'management'];
        $cash_types = ['account', 'cash'];
        $contractorType = ['App\Models\Buyer', 'App\Models\Supplier'];
        $baseType = ['App\Models\Order', 'App\Models\Purchase'];

        foreach(range(1, 120) as $index)
        {
            $incomeExpenseItem = App\Models\IncomeExpenseItem::find( $faker->numberBetween(1, 25) );
            $prefix = $incomeExpenseItem->type == 'income' ? 'I-' : 'O-';

            $cash = new Money;
            $record_type = $record_types[ $faker->numberBetween(0, 1) ];

            $cash->serial = $prefix . str_pad($index, 8, "0", STR_PAD_LEFT);
            $cash->record_type = $record_type;
            $cash->money_type = $cash_types[ $faker->numberBetween(0, 1) ];
            $cash->income_expense_item_id = $incomeExpenseItem->id;

            if ($record_type == 'operational') {
                $cash->contractor_id = $faker->numberBetween(1, 100);
                $cash->contractor_type = $contractorType[ $faker->numberBetween(0, 1) ];
                $cash->base_id = $faker->numberBetween(1, 120);
                $cash->base_type = $baseType[ $faker->numberBetween(0, 1) ];
            }

            $cash->comment = $faker->text;
            $cash->total = $faker->randomFloat(2, 100, 10000);
            $cash->status = $faker->numberBetween(1, 5) != 5 ? 'active' : 'cancelled';

            $cash->created_at = $datetime = $faker->dateTimeBetween('-1 years', 'now');
            $cash->updated_at = $datetime;

            $cash->save();
        }
    }

}