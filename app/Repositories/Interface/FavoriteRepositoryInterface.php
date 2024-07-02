<?php

namespace App\Repositories\interface;

interface FavoriteRepositoryInterface{
    public function addTripToFavorite($id);
    public function addPlaceToFavorite($id);
    public function allFavoriteForMe();
    public function removeTripFromFavorite($id);
    public function removePlaceFromFavorite($id);
}
