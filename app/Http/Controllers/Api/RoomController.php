<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = $request->user()->rooms()->with(['users', 'latestMessage'])->latest()->get();
        return response()->json([
            'rooms' => $rooms
        ], 200);
    }

    public function show(Request $request, $id) {
        $room = $request->user()->rooms()->with('messages')->findOrFail($id);
        return response()->json([
            'room' => $room
        ], 200);
    }

    //create new private room with a friend
    public function store(Request $request)
    {
        $validated = $request->validate([
            'friend_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'in:private'],
        ]);

        $user = $request->user();
        $friendId = (int) $validated['friend_id'];

        if ($user->id === $friendId) {
            return response()->json([
                'message' => 'You cannot create a room with yourself.'
            ], 422);
        }

        return DB::transaction(function () use ($user, $friendId) {
            $existingRoom = Room::query()
                ->where('type', 'private')
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                })
                ->whereHas('users', function ($query) use ($friendId) {
                    $query->where('users.id', $friendId);
                })
                ->has('users', '=', 2)
                ->with('users')
                ->first();

            if ($existingRoom) {
                return response()->json([
                    'room' => $existingRoom,
                    'message' => 'Room already exists',
                ], 200);
            }

            $room = Room::create([
                'type' => 'private',
            ]);

            $room->users()->attach([$user->id, $friendId]);

            return response()->json([
                'room' => $room->load('users'),
                'message' => 'Room created successfully',
            ], 201);
        });
    }

    public function findByUserId(Request $request, $user_id)
    {
        $room = $request->user()
            ->rooms()
            ->with('users')
            ->whereHas('users', function ($query) use ($user_id) {
                $query->where('users.id', $user_id);
            })
            ->first();

        if (!$room) {
            return response()->json([
                'message' => 'Room not found'
            ], 404);
        }

        return response()->json([
            'room' => $room
        ], 200);
    }
}
