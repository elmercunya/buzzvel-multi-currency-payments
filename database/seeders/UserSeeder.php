<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Juan Perez',
            'email' => 'juana@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'country_code' => 'PE',
            'currency_code' => 'PEN'
        ]);

        User::create([
            'name' => 'Lucas Herrera',
            'email' => 'finance@test.com',
            'password' => bcrypt('password'),
            'role' => 'finance',
            'country_code' => 'PT',
            'currency_code' => 'EUR'
        ]);

        User::create([
            'name' => 'Joao Silva',
            'email' => 'joao@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'country_code' => 'BR',
            'currency_code' => 'BRL'
        ]);

        User::create([
            'name' => 'Miguel Ángel Hernández',
            'email' => 'miguel@example.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'country_code' => 'MX',
            'currency_code' => 'MXN'
        ]);

    }
}
