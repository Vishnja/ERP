<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    protected $tables_to_truncate = [
        'roles',
        'users',
        'products',
        'buyers',
        'payment_methods',
        'shipping_methods',
        'order_product',
        'order_statuses',
        'orders',
        'income_expense_items',
        'suppliers',
        'product_supplier_price',
        'purchase_product',
        'purchase_statuses',
        'purchases',
        'money'
    ];

    protected $seeders = [
        'RolesTableSeeder',
        'UsersTableSeeder',

        'ProductsTableSeeder',
        'BuyersTableSeeder',

        'SuppliersTableSeeder',
        'ProductSupplierPriceTableSeeder',
        'PurchaseStatusesTableSeeder',
        'PurchasesTableSeeder',

        'PaymentMethodsTableSeeder',
        'ShippingMethodsTableSeeder',
        'OrderStatusesTableSeeder',
        'OrdersTableSeeder',

        'IncomeExpenseItemsTableSeeder',
        'MoneyTableSeeder',
    ];

    public function run()
    {
        Model::unguard();
        $this->cleanDatabase();

        foreach ($this->seeders as $seedClass)
        {
            $this->call($seedClass);
        }

        Model::reguard();
    }

    private function cleanDatabase()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->tables_to_truncate as $table)
        {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
