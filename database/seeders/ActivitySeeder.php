<?php

namespace Database\Seeders;

use App\Activity;
use App\Cycling;
use App\Hiking;
use App\Running;
use App\Swimming;
use App\User;
use App\Walking;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * 割合: running 3, walking 2, hiking 2, swimming 1, cycling 1
     * 100件: running 34, walking 22, hiking 22, swimming 11, cycling 11
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $this->command->warn('No users found. Run UserSeeder first.');
            return;
        }

        $distribution = [
            'running' => 34,
            'walking' => 22,
            'hiking' => 22,
            'swimming' => 11,
            'cycling' => 11,
        ];

        $titles = [
            'running' => ['Morning Run', 'Evening Jog', '5K Run', 'Interval Training', 'Trail Run', 'Long Run', 'Sprint Session', 'Recovery Run', 'Tempo Run', 'Base Run'],
            'walking' => ['Morning Walk', 'Evening Stroll', 'Park Walk', 'City Walk', 'Nature Walk', 'Power Walk', 'Lunch Break Walk', 'Beach Walk', 'Forest Walk', 'Urban Hike'],
            'hiking' => ['Mountain Trail', 'Forest Hike', 'Valley Trek', 'Summit Challenge', 'Nature Trail', 'Scenic Route', 'Alpine Hike', 'Coastal Path', 'Ridge Walk', 'Canyon Trek'],
            'swimming' => ['Lap Swimming', 'Pool Session', 'Open Water Swim', 'Technique Drill', 'Endurance Swim', 'Sprint Laps', 'Recovery Swim', 'Morning Swim', 'Triathlon Practice', 'Fitness Swim'],
            'cycling' => ['Road Cycling', 'Morning Ride', 'Long Distance', 'Interval Ride', 'Hill Climb', 'Commute Ride', 'Weekend Tour', 'Endurance Ride', 'Recovery Ride', 'Sprint Session'],
        ];

        $descriptions = [
            'Felt great today!',
            'Good workout session.',
            'Building consistency.',
            'Pushed myself a bit.',
            'Relaxing outdoor activity.',
            'Beautiful weather for it.',
            'Working on my form.',
            'Solid effort today.',
        ];

        $hikingLocations = [
            'Mount Fuji Trail',
            'Yosemite National Park',
            'Alps Circuit',
            'Pacific Crest Trail',
            'Appalachian Trail',
            'Kamikochi Valley',
            'Hakone Mountains',
            'Takayama Highlands',
            'Nikko National Park',
            'Oze Marshlands',
        ];

        $totalActivities = array_sum($distribution);
        $startDate = Carbon::now()->subDays(90);
        $endDate = Carbon::now();

        // カテゴリが連続しないよう、全アクティビティを先に配列化してシャッフル
        $activitiesToCreate = [];
        $index = 0;
        foreach ($distribution as $category => $count) {
            for ($i = 0; $i < $count; $i++) {
                $index++;
                $activitiesToCreate[] = [
                    'category' => $category,
                    'title' => $titles[$category][array_rand($titles[$category])] . ' #' . $index,
                    'description' => $descriptions[array_rand($descriptions)],
                ];
            }
        }
        shuffle($activitiesToCreate);

        foreach ($activitiesToCreate as $idx => $item) {
            $category = $item['category'];
            $title = $item['title'];
            $description = $item['description'];

            // 90日前から今日まで均等に作成日をばらつかせる
            $progress = $totalActivities > 1 ? $idx / ($totalActivities - 1) : 1;
            $createdAt = $startDate->copy()->addSeconds(
                (int) (($endDate->timestamp - $startDate->timestamp) * $progress)
            );

            DB::transaction(function () use ($user, $category, $title, $description, $hikingLocations, $createdAt) {
                    $activity = Activity::create([
                        'user_id' => $user->id,
                        'title' => $title,
                        'description' => $description,
                        'category' => $category,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    [$distance, $duration] = $this->generateRealisticDistanceAndDuration($category);

                    match ($category) {
                        'running' => Running::create([
                            'activity_id' => $activity->id,
                            'distance' => $distance,
                            'duration' => $duration,
                        ]),
                        'walking' => Walking::create([
                            'activity_id' => $activity->id,
                            'distance' => $distance,
                            'duration' => $duration,
                        ]),
                        'cycling' => Cycling::create([
                            'activity_id' => $activity->id,
                            'distance' => $distance,
                            'duration' => $duration,
                        ]),
                        'swimming' => Swimming::create([
                            'activity_id' => $activity->id,
                            'distance' => round($distance / 10, 2), // swimming is typically shorter distances
                            'duration' => $duration,
                        ]),
                        'hiking' => Hiking::create([
                            'activity_id' => $activity->id,
                            'distance' => $distance,
                            'duration' => $duration,
                            'location' => $hikingLocations[array_rand($hikingLocations)],
                        ]),
                        default => null,
                    };
                });
        }

        $this->command->info('Created 100 activities with related detail records.');
    }

    /**
     * カテゴリに応じた現実的な距離・時間の組み合わせを生成
     * duration を先に決め、速度から distance を算出
     */
    private function generateRealisticDistanceAndDuration(string $category): array
    {
        $duration = match ($category) {
            'running' => mt_rand(20, 90),   // 20-90分（ジョギング〜マラソン練習）
            'walking' => mt_rand(15, 75),   // 15-75分（散歩〜ウォーキング）
            'hiking' => mt_rand(60, 240),   // 1-4時間（ハイキング）
            'swimming' => mt_rand(30, 90),  // 30-90分（プール）
            'cycling' => mt_rand(30, 150),  // 30-150分（サイクリング）
            default => mt_rand(30, 60),
        };

        // 速度(km/h): アマチュア〜中級者程度の現実的な範囲
        $speedMinMax = match ($category) {
            'running' => [8.0, 12.0],   // 5:00-7:30/km ペース
            'walking' => [4.0, 6.0],    // 10:00-15:00/km
            'hiking' => [2.5, 4.0],     // 15:00-24:00/km（山道）
            'swimming' => [1.5, 3.0],   // 2-4km/h（プール）
            'cycling' => [18.0, 28.0],  // 18-28km/h（サイクリング）
            default => [5.0, 10.0],
        };

        $speed = $speedMinMax[0] + (mt_rand() / mt_getrandmax()) * ($speedMinMax[1] - $speedMinMax[0]);
        $distance = round(($duration / 60) * $speed, 2);

        return [$distance, $duration];
    }
}
