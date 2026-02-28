<?php

namespace App\Http\Controllers\Api;

use App\Activity;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Activity::with('User')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $activity = Activity::create(
            [
                "title" => $request -> title,
                "description" => $request -> description,
                "user_id" => $user->id
            ]
        );
        return response()->json([
            "message"=> "Activity was created successfully",
            "activity" => $activity
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Activity::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);
        $activity->update(
            [
                "title" => $request -> title,
                "description" => $request -> description
            ]
        );
        return response()->json(
            [
                "message"=> "Activity was updated successfully",
                "activity" => $activity
            ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $activity = Activity::findOrFail($id);
        $activity->delete();
        return response()->json([
            "message"=> "Activity was deleted successfully",
            "activity" => $activity
        ], 200);
    }
}
