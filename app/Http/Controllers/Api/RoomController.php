<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = $request->user()->rooms()->with('messages')->latest()->get();
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
    public function store(Request $request) {
        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);
        $room = Room::create([
            'type' => $request->type,
        ]);
        $room->users()->attach($request->user()->id, $request->friend_id);
        return response()->json([
            'room' => $room
        ], 200);
    }
}
