<?php

namespace Database\Seeders;

use App\Room;
use App\User;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hidekazu = User::find(1);
        $others = User::whereIn('id', [2, 3, 4, 5, 6])->get();

        foreach ($others as $other) {
            $room = Room::create(['type' => 'private']);
            $room->users()->attach([$hidekazu->id, $other->id]);
        }
    }
}
