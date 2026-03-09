<?php

namespace App\Http\Controllers\Api;

use App\Friends;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FriendRequestController extends Controller
{
    //private function to get the received friend requests
    private function received_friend_requests($request){
        return $request->user()->friend_requests()
        ->where("status", "pending")
        ->where("receiver_id", $request->user()->id)
        ->with("sender")
        ->get();
    }


    //public function to get the sent friend requests
    public function index_sent(Request $request){
        $sent_friend_requests = $request->user()->friend_requests()
        ->where("status", "pending")
        ->where("sender_id", $request->user()->id)
        ->with("receiver")
        ->get();

        return response()->json([
            'sent_friend_requests' => $sent_friend_requests
        ], 200);
    }
    //public function to get the received friend requests
    public function index_received(Request $request){
        $received_friend_requests = $this->received_friend_requests($request);
        return response()->json([
            'received_friend_requests' => $received_friend_requests
        ], 200);
    }
    //public function to send a friend request
    public function send(Request $request){
        $friend_request = $request->user()->friend_requests()->create([
            'receiver_id' => $request->receiver_id,
            'status' => "pending"
        ]);
        return response()->json([
            'friend_request' => $friend_request
        ], 201);
    }
    //public function to accept a friend request
    public function accept(Request $request){
        $friend_request = $this->received_friend_requests($request)->where("id", $request->id)->first();
        if(!$friend_request){
            return response()->json([
                'message' => "Friend request not found"
            ], 404);
        }

        //update the friend request status to accepted
        $friend_request->status = "accepted";
        $friend_request->save();

        //create both friends records
        Friends::create([
            'user_id' => $request->user()->id,
            'friend_id' => $friend_request->sender_id
        ]);
        Friends::create([
            'user_id' => $friend_request->sender_id,
            'friend_id' => $request->user()->id
        ]);

        return response()->json([
            'message' => "Friend request accepted"
        ], 200);
    }
    //public function to reject a friend request
    public function reject(Request $request){
        $friend_request = $this->received_friend_requests($request)->where("id", $request->id)->first();
        if(!$friend_request){
            return response()->json([
                'message' => "Friend request not found"
            ], 404);
        }
        //update the friend request status to rejected
        $friend_request->status = "rejected";
        $friend_request->save();

        return response()->json([
            'message' => "Friend request rejected"
        ], 200);
    }
}