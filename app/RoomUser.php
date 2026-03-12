<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoomUser extends Pivot
{
    protected $table = 'room_user';

    public $timestamps = true;

    protected $fillable = [
        'room_id',
        'user_id',
        'last_read_at',
        'joined_at',
        'role',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'joined_at' => 'datetime',
    ];
}
