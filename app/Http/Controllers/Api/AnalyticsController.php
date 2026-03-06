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

        // Weekly activity total number (7 days ago, Monday–Sunday)
        $last7DaysActivityTotal = $user->activities()
            ->whereBetween('created_at', [now()->subDays(7), now()])
            ->count();

        // Monthly acticity total number  (30 days ago, 01 - end of month)
        $last30DaysActivityTotal = $user->activities()
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->count();

        // Monthly acticity total number  (60 days ago, 01 - end of month)
        $last60DaysActivityTotal = $user->activities()
            ->whereBetween('created_at', [now()->subDays(60), now()])
            ->count();

        // Monthly acticity total number  (90 days ago, 01 - end of month)
        $last90DaysActivityTotal = $user->activities()
            ->whereBetween('created_at', [now()->subDays(90), now()])
            ->count();

        // total number of each category's activity (7 days ago, Monday–Sunday)
        $last7DaysCategoryActivityTotal = $user->activities()
            ->whereBetween('created_at', [now()->subDays(7), now()])
            ->groupBy('category')
            ->select('category', DB::raw('COUNT(*) as total'))
            ->get();

        // total number of each category's activity (30 days ago, 01 - end of month)
        $last30DaysCategoryActivityTotal = $user->activities()
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->groupBy('category')
            ->select('category', DB::raw('COUNT(*) as total'))
            ->get();

        // total number of each category's activity (60 days ago, 01 - end of month)
        $last60DaysCategoryActivityTotal = $user->activities()
            ->whereBetween('created_at', [now()->subDays(60), now()])
            ->groupBy('category')
            ->select('category', DB::raw('COUNT(*) as total'))
            ->get();
        // total number of each category's activity (90 days ago, 01 - end of month)
        $last90DaysCategoryActivityTotal = $user->activities()
            ->whereBetween('created_at', [now()->subDays(90), now()])
            ->groupBy('category')
            ->select('category', DB::raw('COUNT(*) as total'))
            ->get();



        // 30 days daily distance & duration (not sum, just get the values)
        $categories = [
            'running',
            'walking',
            'cycling',
            'hiking',
            'swimming',
        ];
        
        $queries = [];
        
        foreach ($categories as $category) {
        
            $table = $category . 's';
        
            $queries[] = DB::table('activities')
                ->join($table, "$table.activity_id", '=', 'activities.id')
                ->where('activities.user_id', $user->id)
                ->where('activities.category', $category)
                ->whereBetween('activities.created_at', [now()->subDays(90), now()])
                ->selectRaw("
                    DATE(activities.created_at) as date,
                    SUM($table.distance) as distance,
                    SUM($table.duration) as duration
                ")
                ->groupByRaw('DATE(activities.created_at)');
        }
        
        $query = array_shift($queries);
        
        foreach ($queries as $q) {
            $query->unionAll($q);
        }
        
        $dailyDistanceAndDurationValues = DB::query()
            ->fromSub($query, 't')
            ->selectRaw('date, SUM(distance) as distance, SUM(duration) as duration')
            ->groupBy('date')
            ->orderBy('date')
            ->get();


        return response()->json([
            'last7DaysActivityTotal' => $last7DaysActivityTotal,
            'last30DaysActivityTotal' => $last30DaysActivityTotal,
            'last60DaysActivityTotal' => $last60DaysActivityTotal,
            'last90DaysActivityTotal' => $last90DaysActivityTotal,
            'last7DaysCategoryActivityTotal' => $last7DaysCategoryActivityTotal,
            'last30DaysCategoryActivityTotal' => $last30DaysCategoryActivityTotal,
            'last60DaysCategoryActivityTotal' => $last60DaysCategoryActivityTotal,
            'last90DaysCategoryActivityTotal' => $last90DaysCategoryActivityTotal,
            'dailyDistanceAndDurationValues' => $dailyDistanceAndDurationValues,
        ]);
    }
}
