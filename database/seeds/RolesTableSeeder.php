<?php
use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder {

    public function run()
    {
        $capabilities = [
            "dashboard"                     => "true",
            "orders-index"                  => "true",
            "purchases-index"               => "true",
            "money-index"                   => "true",
            "users-index"                   => "true",
            "roles-index"                   => "true",
            "suppliers-index"               => "true",
            "buyers-index"                  => "true",
            "products-index"                => "true",
            "productSupplierPrice-index"    => "true",
            "incomeExpenseItems-index"      => "true",
            "delete-records"                => "true",
            "watch-history"                 => "true"
        ];

        Role::create([
            'name' => 'Суперадмин',
            'capabilities' => $capabilities
        ]);

        Role::create([
            'name' => 'Директор',
            'capabilities' => $capabilities
        ]);

        Role::create([
            'name' => 'Менеджер по работе с клиентами',
            'capabilities' => $capabilities
        ]);

        Role::create([
            'name' => 'Менеджер по закупкам',
            'capabilities' => $capabilities
        ]);
    }

}
