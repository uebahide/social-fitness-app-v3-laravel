<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        $request->validate(['image' => 'required|image|max:2048']);
        $user = $request->user();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads', 'public');
            $user->update([
                "image_path" =>Storage::url($path)
            ]);
            return response()->json(['url' => asset('storage/' . $path)], 200);
        }
    }
}
