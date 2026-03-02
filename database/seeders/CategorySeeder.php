<?php

namespace Database\Seeders;

use App\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            "Running",
            "Walking",
            "Cycling",
            "Swimming",
            "Gym",
            "Yoga",
            "Boxing",
            "Hiking"
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(
                ['name' => $name],
                ['name' => $name]
            );
        }
    }
}
