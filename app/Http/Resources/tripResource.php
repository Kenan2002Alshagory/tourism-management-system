<?php

namespace App\Http\Resources;

use App\Models\Favorite;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class tripResource extends JsonResource
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
            "user_id" => $this->user_id,
            "trip_name" => $this->trip_name,
            "start_date" => $this->start_date,
            "end_date" => $this->end_date,
            "duration" => $this->duration,
            "from" => $this->from,
            "destination" => $this->destination,
            "guide_name" => $this->guide_name,
            "travelers_num" => $this->travelers_num,
            "trip_type" => $this->trip_type,
            "trip_price" => $this->trip_price,
            "trip_status" => $this->trip_status,
            "status_time" => $this->status_time,
            "trip_description" => $this->trip_description,
            "trip_image" => $this->trip_image,
            'fav' =>Favorite::where('user_id',Auth::user()->id)
            ->where('favoritable_type',Trip::class)
            ->where('favoritable_id',$this->id)
            ->count() == 0 ? false : true,
        ];
    }
}
