<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function update(Request $request){
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);
        $user->update([
            "name" => $validated['name'],
            "email" => $validated['email']
        ]);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
        ], 200);
    }

    public function updateImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:1024'],
        ]);

        $user = $request->user();

        if (!empty($user->image_path)) {
            Storage::disk('s3')->delete($user->image_path);
        }

        $path = $request->file('image')->store("avatars/{$user->id}", 's3');

        if ($path === false) {
            return response()->json([
                'error' => 'Upload failed'
            ], 500);
        }

        $user->image_path = $path;
        $user->save();

        return response()->json([
            'image_path' => $path,
            'url' => Storage::disk('s3')->url($path),
        ]);
    }

    public function search(Request $request){
        $query = $request->query('query');
        $users = User::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%'])->where("id", "!=", $request->user()->id)
        ->with("friend_requests_received")->with("friend_requests_sent")->with("friends")->get();
        return response()->json([
            'users' => $users
        ], 200);
    }
}
