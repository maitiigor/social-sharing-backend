<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'caption' => $this->caption,
            'location' => $this->location,
            'peopleTagged' => $this->users->pluck('name')->toArray(),
            'images' => $this->images->map(function ($image) {
                return route('image.show' ,$image->id);
            })->toArray(),
            'imageUrl' => $this->images->first()->url ? route('image.show',$this->images->first()->id) : null,
            'user_id' => $this->user_id,
            'creatorName' => $this->creatorName,
            'likes' => $this->likes()->count(),
            'rating' => $this->rating,
           
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
