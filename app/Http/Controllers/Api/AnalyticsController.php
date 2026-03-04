<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Weekly activity total number (current week, Monday–Sunday)
        $weeklyActivityTotal = $user->activities()
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // Monthly acticity total number  (current month, 01 - end of month)
        $monthlyActivityTotal = $user->activities()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        // total number of each category's activity (current month)
        $monthlyCategoryActivityTotal = $user->activities()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('category')
            ->select('category', DB::raw('COUNT(*) as total'))
            ->get();

        // total number of each category's activity (current week)
        $weeklyCategoryActivityTotal = $user->activities()
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->groupBy('category')
            ->select('category', DB::raw('COUNT(*) as total'))
            ->get();


        // 30 days daily distance & duration (not sum, just get the values)
        $dailyDistanceAndDurationValues = $user->activities()
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(distance) as distance'),
                DB::raw('SUM(duration) as duration')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();


        return response()->json([
            'monthly_activity_total' => $monthlyActivityTotal,
            'weekly_activity_total' => $weeklyActivityTotal,
            'monthly_category_activity_total' => $monthlyCategoryActivityTotal,
            'weekly_category_activity_total' => $weeklyCategoryActivityTotal,
            'daily_distance_and_duration_values' => $dailyDistanceAndDurationValues,
        ]);
    }
}
