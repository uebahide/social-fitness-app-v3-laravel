<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'type',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'room_user')
        ->using(RoomUser::class)    
        ->withTimestamps();
    }
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
