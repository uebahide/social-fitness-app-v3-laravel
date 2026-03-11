<?php

namespace App\Http\Controllers\Api;

use App\Friend_requests;
use App\Friends;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FriendRequestController extends Controller
{
    //private function to get the received friend requests
    private function received_friend_requests($request){
        return $request->user()->friend_requests_received()
        ->where("status", "pending")
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
    public function send(Request $request, String $receiver_id){
        //console log the receiver_id
        Log::info('Receiver ID: ' . $receiver_id);
        if($request->user()->id == $receiver_id){
            return response()->json([
                'message' => "You cannot send a friend request to yourself"
            ], 400);
        }
        if(Friend_requests::where("receiver_id", $receiver_id)->where("sender_id", $request->user()->id)->where("status", "pending")->exists()){
            return response()->json([
                'message' => "Friend request already sent"
            ], 400);
        }
        if(Friend_requests::where("receiver_id", $receiver_id)->where("sender_id", $request->user()->id)->where("status", "accepted")->exists()){
            return response()->json([
                'message' => "Friend already accepted"
            ], 400);
        }
        if(Friend_requests::where("receiver_id", $request->user()->id)->where("sender_id", $receiver_id)->exists()){
            return response()->json([
                'message' => "You have a friend request from this user already"
            ], 400);
        }
        // if(Friend_requests::where("receiver_id", $receiver_id)->where("sender_id", $request->user()->id)->where("status", "rejected")->exists()){
        //     return response()->json([
        //         'message' => "Friend request rejected"
        //     ], 400);
        // }

        $friend_request = Friend_requests::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $receiver_id,
            'status' => "pending"
        ]);
        return response()->json([
            'friend_request' => $friend_request
        ], 201);
    }
    //public function to accept a friend request
    public function accept(Request $request, String $request_id){
        $friend_request = $this->received_friend_requests($request)->where("id", $request_id)->first();
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
    public function reject(Request $request, String $request_id){
        $friend_request = $this->received_friend_requests($request)->where("id", $request_id)->first();
        if(!$friend_request){
            return response()->json([
                'message' => "Friend request not found"
            ], 404);
        }
        //update the friend request status to rejected
        $friend_request->delete();

        return response()->json([
            'message' => "Friend request rejected"
        ], 200);
    }

    public function search(Request $request){
        $query = $request->query('query');
        $friend_requests = $this->received_friend_requests($request);
        $friend_requests = $friend_requests->filter(function($friend_request) use ($query){
            return str_contains(strtolower($friend_request->sender->name), strtolower($query));
        })->values();
        return response()->json([
            'friend_requests' => $friend_requests
        ]);
    }
}