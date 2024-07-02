<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'trip_name', 'start_date', 'end_date', 'duration', 'from', 'destination', 'guide_name', 'travelers_num', 'trip_type', 'trip_price', 'trip_status', 'trip_description', 'trip_image','status_time'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function tripItineraries()
    {
        return $this->hasMany(TripItinerary::class);
    }
}
