<?php

namespace App\Repositories\interface;

interface PlaceRepositoryInterface{
    public function searchHotel($search);
    public function searchRestaurant($search);
    public function searchFamous_place($search);
}
