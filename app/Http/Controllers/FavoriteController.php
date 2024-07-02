<?php

namespace App\Http\Controllers;

use App\Repositories\interface\FavoriteRepositoryInterface;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    private $favoriteRepository;

    public function __construct(FavoriteRepositoryInterface $favoriteRepository)
    {
        $this->favoriteRepository = $favoriteRepository;
    }

    public function addTripToFavorite($id){
        return $this->favoriteRepository->addTripToFavorite($id);
    }

    public function addPlaceToFavorite($id){
        return $this->favoriteRepository->addPlaceToFavorite($id);
    }

    public function allFavoriteForMe(){
        return $this->favoriteRepository->allFavoriteForMe();
    }


    public function removeTripFromFavorite($id){
        return $this->favoriteRepository->removeTripFromFavorite($id);
    }

    public function removePlaceFromFavorite($id){
        return $this->favoriteRepository->removePlaceFromFavorite($id);
    }
}
