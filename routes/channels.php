<?php

use App\Room;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{roomId}', function ($user, int $roomId) {
    return Room::whereKey($roomId)
        ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
        ->exists();
});