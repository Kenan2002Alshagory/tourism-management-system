<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Repositories\interface\PlaceRepositoryInterface;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    private $placeRepository;
    public function __construct(PlaceRepositoryInterface $placeRepository)
    {
        $this->placeRepository = $placeRepository;
    }

    public function searchHotel(Request $request){
        return $this->placeRepository->searchHotel($request->search);
    }

    public function searchRestaurant(Request $request){
        return $this->placeRepository->searchRestaurant($request->search);
    }

    public function searchFamous_place(Request $request){
        return $this->placeRepository->searchFamous_place($request->search);
    }
}
