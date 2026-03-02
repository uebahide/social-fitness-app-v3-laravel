<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Activity extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'user_id'
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category');
    }

    public function runnings(): HasOne
    {
        return $this->hasOne(Running::class, 'activity_id');
    }

    public function walkings(): HasOne
    {
        return $this->hasOne(Walking::class, 'activity_id');
    }

    public function cyclings(): HasOne
    {
        return $this->hasOne(Cycling::class, 'activity_id');
    }

    public function swimmings(): HasOne
    {
        return $this->hasOne(Swimming::class, 'activity_id');
    }

    public function hikings(): HasOne
    {
        return $this->hasOne(Hiking::class, 'activity_id');
    }
}


