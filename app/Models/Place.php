<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'type', 'location', 'description', 'rating', 'photo_url'];


    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function tripItinerarys()
    {
        return  $this->hasMany(TripItinerary::class);
    }

    public function scopeHotel($query)
    {
        return $query->where('type', 'hotel');
    }

    public function scopeActivity($query)
    {
        return $query->where('type', 'activity');
    }

    public function scopeRestaurant($query)
    {
        return $query->where('type', 'restaurant');
    }

    public function scopeFamous_place($query)
    {
        return $query->where('type', 'famous_place');
    }
}




