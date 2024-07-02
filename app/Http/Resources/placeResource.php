<?php

namespace App\Http\Resources;

use App\Models\Favorite;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class placeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "type" => $this->type,
            "location"=> $this->location,
            "description"=> $this->description,
            "rating" => $this->rating,
            "photo_url" => $this->photo_url,
            'fav' =>Favorite::where('user_id',Auth::user()->id)
            ->where('favoritable_type',Place::class)
            ->where('favoritable_id',$this->id)
            ->count() == 0 ? false : true,
        ];
    }
}
