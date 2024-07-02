<?php

namespace App\Repositories\interface;

interface TripRepositoryInterface{
    public function searchTrip($search);
    public function detailForDay($tripId,$dayNum);
}
