<?php

namespace Database\Seeders;

use App\Message;
use App\Room;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates 10 message exchanges (20 messages) per room.
     */
    public function run(): void
    {
        $messagePairs = [
            ['今日のランニングお疲れ様！', 'ありがとう！5km走れたよ'],
            ['いいね！ペースどうだった？', '1km 5:30くらいだった'],
            ['めっちゃいいペースじゃん', '明日一緒に走る？'],
            ['いいよ！何時から？', '7時くらいどう？'],
            ['了解、その時間で！', 'よろしく！'],
            ['筋トレ頑張ってる？', 'うん、週3でやってる'],
            ['すごい！何してる？', 'スクワットと腕立て'],
            ['ダイエット進んでる？', '2kg減った！'],
            ['おお、すごい！', '食事管理が効いてる'],
            ['続けて！お互い頑張ろう', 'うん、ありがとう！'],
        ];

        $rooms = Room::with('users')->get();

        foreach ($rooms as $room) {
            $users = $room->users->pluck('id')->toArray();
            $createdAt = now()->subDays(7);

            for ($i = 0; $i < 10; $i++) {
                $pair = $messagePairs[$i];

                Message::create([
                    'room_id' => $room->id,
                    'user_id' => $users[0],
                    'body' => $pair[0],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $createdAt = $createdAt->copy()->addMinutes(rand(1, 30));

                Message::create([
                    'room_id' => $room->id,
                    'user_id' => $users[1],
                    'body' => $pair[1],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $createdAt = $createdAt->copy()->addHours(rand(2, 12));
            }
        }
    }
}
