<?php

namespace App\Http\Controllers\Api;

use App\Activity;
use App\Cycling;
use App\Hiking;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Running;
use App\Swimming;
use App\Walking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $activities = $request->user()->activities()->with([
            'runnings',
            'walkings',
            'cyclings',
            'swimmings',
            'hikings',
        ])->latest()->get();

        return ActivityResource::collection($activities);
    }

    public function paginated(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $activities = $request->user()->activities()->with([
            'runnings',
            'walkings',
            'cyclings',
            'swimmings',
            'hikings',
        ])->latest('id')->paginate($perPage);

        return ActivityResource::collection($activities);
    }

    public function count(Request $request){
        $activities = $request->user()->activities()->count();
        return response()->json([
            'count' => $activities
        ], 200);
    }

    //return the latest activity for the user
    public function latest(Request $request){
        $activity = $request->user()->activities()->latest()->first();
        return response()->json([
            'activity' => $activity
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $user = $request->user(); 

        $request->validate([
            'title' => ['nullable','string','max:255'],
            'description' => ['nullable','string'],
            'category' => ['required','string'],
        ]);

        $category = Str::lower(trim($request->category));

        [$activity, $detail] = DB::transaction(function () use ($request, $user, $category) {

            $activity = Activity::create([
                'title' => $request->title,
                'description' => $request->description,
                'category' => $category,   // ← DBにも正規化した値で保存
                'user_id' => $user->id,
            ]);

            // 共通（距離/時間）が必要なカテゴリ
            if (in_array($category, ['running','walking','cycling','swimming','hiking'], true)) {
                $request->validate([
                    'distance' => ['numeric','min:0', 'nullable'],
                    'duration' => ['integer','min:0', 'nullable'],
                ]);
            }

            $detail = null;

            if ($category === 'running') {
                $detail = Running::create([
                    'activity_id' => $activity->id,
                    'distance' => $request->distance,
                    'duration' => $request->duration,
                ]);
            } elseif ($category === 'walking') {
                $detail = Walking::create([
                    'activity_id' => $activity->id,
                    'distance' => $request->distance,
                    'duration' => $request->duration,
                ]);
            } elseif ($category === 'cycling') {
                $detail = Cycling::create([
                    'activity_id' => $activity->id,
                    'distance' => $request->distance,
                    'duration' => $request->duration,
                ]);
            } elseif ($category === 'swimming') {
                $detail = Swimming::create([
                    'activity_id' => $activity->id,
                    'distance' => $request->distance,
                    'duration' => $request->duration,
                ]);
            } elseif ($category === 'hiking') {
                $request->validate([
                    'location' => ['string','max:255', 'nullable'],
                ]);

                $detail = Hiking::create([
                    'activity_id' => $activity->id,
                    'distance' => $request->distance,
                    'duration' => $request->duration,
                    'location' => $request->location,
                ]);
            }

            return [$activity, $detail];
        });

        return response()->json([
            'message' => 'Activity was created successfully',
            'activity' => $activity,
            'detail' => $detail,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $activity = $request->user()->activities()->with([
            'runnings',
            'walkings',
            'cyclings',
            'swimmings',
            'hikings',
        ])->findOrFail($id);

        return new ActivityResource($activity);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $activity = $request->user()->activities()->findOrFail($id);

        $activity->update(
            [
                "title" => $request -> title,
                "description" => $request -> description,
            ]
        );
        
        $category = Str::lower(trim($request->category));
        $detail = null;

        if ($category === 'running') {
            $detail = Running::where('activity_id', $activity->id)->update([
                'activity_id' => $activity->id,
                'distance' => $request->details['distance'],
                'duration' => $request->details['duration'],
            ]);
        } elseif ($category === 'walking') {
            $detail = Walking::where('activity_id', $activity->id)->update([
                'activity_id' => $activity->id,
                'distance' => $request->details['distance'],
                'duration' => $request->details['duration'],
            ]);
        } elseif ($category === 'cycling') {
            $detail = Cycling::where('activity_id', $activity->id)->update([
                'activity_id' => $activity->id,
                'distance' => $request->details['distance'],
                'duration' => $request->details['duration'],
            ]);
        } elseif ($category === 'swimming') {
            $detail = Swimming::where('activity_id', $activity->id)->update([
                'activity_id' => $activity->id,
                'distance' => $request->details['distance'],
                'duration' => $request->details['duration'],
            ]);
        } elseif ($category === 'hiking') {
            $request->validate([
                'location' => ['string','max:255', 'nullable'],
            ]);

            $detail = Hiking::where('activity_id', $activity->id)->update([
                'activity_id' => $activity->id,
                'distance' => $request->details['distance'],
                'duration' => $request->details['duration'],
                'location' => $request->details['location'],
            ]);
        }


        return response()->json([
            'message' => 'Activity was updated successfully',
            'activity' => $activity,
            'detail' => $detail,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $activity = $request->user()->activities()->findOrFail($id);
        $activity->delete();
        return response()->json([
            "message"=> "Activity was deleted successfully",
            "activity" => $activity
        ], 200);
    }
}
