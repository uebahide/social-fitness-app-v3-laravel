<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Hidekazu Ueba', 'email' => 'test@test.com'],
            ['name' => 'Alice Tanaka', 'email' => 'alice@test.com'],
            ['name' => 'Bob Suzuki', 'email' => 'bob@test.com'],
            ['name' => 'Carol Yamada', 'email' => 'carol@test.com'],
            ['name' => 'David Sato', 'email' => 'david@test.com'],
            ['name' => 'Eve Watanabe', 'email' => 'eve@test.com'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
