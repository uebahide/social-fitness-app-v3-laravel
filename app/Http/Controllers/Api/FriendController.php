<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    //index
    public function index(Request $request){
        $friends = $request->user()->friends()->where("user_id", $request->user()->id)->with("friend")->get();
        return response()->json([
            'friends' => $friends
        ], 200);
    }

    public function search(Request $request){
        $query = $request->query('query');
        $friend_tables_with_friend_info = $request->user()->friends()->with("friend")->get();
        $friends = $friend_tables_with_friend_info->map(function($friend_table){
            return $friend_table->friend;
        })->filter(function($friend) use ($query){
            return str_contains(strtolower($friend->name), strtolower($query));
        })->values();
        return response()->json([
            'friends' => $friends
        ]);
    }
}
