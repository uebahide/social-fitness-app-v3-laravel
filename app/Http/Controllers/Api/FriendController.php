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
}
