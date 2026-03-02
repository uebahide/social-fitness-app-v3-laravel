<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'image_path' => $this->user->image_path,
            ],
        

            'details' => match(Str::lower($this->category)) {
                'running' => $this->runnings->only('distance', 'duration'),
                'walking' => $this->walkings->only('distance', 'duration'),
                'cycling' => $this->cyclings->only('distance', 'duration'),
                'swimming' => $this->swimmings->only('distance', 'duration'),
                'hiking' => $this->hikings->only('distance', 'duration', 'location'),
                default => null,
            },

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
