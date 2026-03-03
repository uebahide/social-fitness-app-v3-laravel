<?php

namespace Database\Seeders;

use App\Activity;
use App\Cycling;
use App\Hiking;
use App\Running;
use App\Swimming;
use App\User;
use App\Walking;
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

        $index = 0;
        foreach ($distribution as $category => $count) {
            for ($i = 0; $i < $count; $i++) {
                $index++;
                $title = $titles[$category][array_rand($titles[$category])] . ' #' . $index;
                $description = $descriptions[array_rand($descriptions)];

                DB::transaction(function () use ($user, $category, $title, $description, $hikingLocations) {
                    $activity = Activity::create([
                        'user_id' => $user->id,
                        'title' => $title,
                        'description' => $description,
                        'category' => $category,
                    ]);

                    $distance = round(mt_rand(100, 5000) / 100, 2); // 1.00 - 50.00 km
                    $duration = mt_rand(15, 180); // 15 - 180 minutes

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

        $this->command->info('Created 100 activities with related detail records.');
    }
}
