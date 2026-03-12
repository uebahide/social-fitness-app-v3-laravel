<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'image_path','password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function friend_requests_received(): HasMany
    {
        return $this->hasMany(Friend_requests::class, 'receiver_id');
    }

    public function friend_requests_sent(): HasMany
    {
        return $this->hasMany(Friend_requests::class, 'sender_id');
    }

    public function friends(): HasMany  {
        return $this->hasMany(Friends::class, 'user_id');
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_user')
        ->using(RoomUser::class)
        ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}


