<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Message;
use Illuminate\Http\Request;
use App\Http\Resources\MessageResource;
class MessageController extends Controller
{
    public function index(Request $request, String $room_id)
    {
        $perPage = $request->input('per_page', 20);
        $messages = Message::where('room_id', $room_id)->with('user')->get();
        return MessageResource::collection($messages);
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

        // broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => $message
        ], 200);
    }
}
