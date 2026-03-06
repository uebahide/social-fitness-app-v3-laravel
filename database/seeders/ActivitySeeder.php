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
     * 過去100日間の記録。日によって 0件・1件・2件の activity がランダムに分布。
     * カテゴリ割合: running 3, walking 2, hiking 2, swimming 1, cycling 1
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $this->command->warn('No users found. Run UserSeeder first.');
            return;
        }

        $categories = ['running', 'walking', 'hiking', 'swimming', 'cycling'];

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

        $days = 100;
        // 各日の activity 数: 0 = 休み(35%), 1 = 1件(45%), 2 = 2件(20%)
        $dailyCounts = [];
        for ($d = 0; $d < $days; $d++) {
            $r = mt_rand(1, 100);
            if ($r <= 35) {
                $dailyCounts[] = 0;
            } elseif ($r <= 80) {
                $dailyCounts[] = 1;
            } else {
                $dailyCounts[] = 2;
            }
        }

        $totalActivities = array_sum($dailyCounts);
        $index = 0;
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

        for ($d = 0; $d < $days; $d++) {
            $date = $startDate->copy()->addDays($d);
            $countToday = $dailyCounts[$d];

            for ($c = 0; $c < $countToday; $c++) {
                $index++;
                $category = $categories[array_rand($categories)];
                $title = $titles[$category][array_rand($titles[$category])] . ' #' . $index;
                $description = $descriptions[array_rand($descriptions)];

                // 同日に複数ある場合は時間をずらす（朝・夕など）
                $hour = $countToday === 2 && $c === 0 ? mt_rand(6, 9) : mt_rand(15, 20);
                $minute = mt_rand(0, 59);
                $createdAt = $date->copy()->setTime($hour, $minute, 0);

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
        }

        $this->command->info("Created {$totalActivities} activities over the past {$days} days (some days with 0, some with 2).");
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
