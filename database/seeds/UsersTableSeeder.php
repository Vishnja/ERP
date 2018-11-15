<?php
use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder {

    public function run()
    {
        User::create([
            'name' => 'Иван',
            'surname' => 'Иванов',
            'email' => 'admin@test.com',
            'role_id' => '1', // Суперадмин
            'photo' => '',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Петр',
            'surname' => 'Петров',
            'email' => 'test@test.com',
            'role_id' => '4', // Менеджер по закупкам
            'photo' => '',
            'password' => Hash::make('password'),
        ]);
    }

}
