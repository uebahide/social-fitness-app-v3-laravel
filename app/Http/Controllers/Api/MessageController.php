<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request, String $room_id)
    {
        $messages = Message::where('room_id', $room_id)->with('user')->get();
        return response()->json([
            'messages' => $messages
        ], 200);
    }

    public function store(Request $request, String $room_id)
    {
        $request->validate([
            'body' => 'required|string',
        ]);
        $message = Message::create([
            'room_id' => $room_id,
            'user_id' => $request->user()->id,
            'body' => $request->body,
        ]);
        return response()->json([
            'message' => $message
        ], 200);
    }
}
