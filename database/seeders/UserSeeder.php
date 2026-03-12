<?php

namespace Database\Seeders;

use App\Friends;
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
            ['name' => 'Alice Smith', 'email' => 'alice@test.com'],
            ['name' => 'Bob Johnson', 'email' => 'bob@test.com'],
            ['name' => 'Carol Williams', 'email' => 'carol@test.com'],
            ['name' => 'David Brown', 'email' => 'david@test.com'],
            ['name' => 'Eve Davis', 'email' => 'eve@test.com'],
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

        $faker = \Faker\Factory::create('en_US');
        for ($i = 7; $i <= 100; $i++) {
            User::updateOrCreate(
                ['email' => "user{$i}@test.com"],
                [
                    'name' => $faker->name(),
                    'password' => Hash::make('password'),
                ]
            );
        }

        // Make 50 users friends with Hidekazu Ueba
        $hidekazu = User::where('email', 'test@test.com')->first();
        $friendsToAdd = User::where('id', '!=', $hidekazu->id)->take(50)->get();

        foreach ($friendsToAdd as $friend) {
            if (!Friends::where('user_id', $hidekazu->id)->where('friend_id', $friend->id)->exists()) {
                Friends::create(['user_id' => $hidekazu->id, 'friend_id' => $friend->id]);
                Friends::create(['user_id' => $friend->id, 'friend_id' => $hidekazu->id]);
            }
        }
    }
}
