<?php

namespace App\Http\Controllers;

use App\Repositories\interface\TripRepositoryInterface;
use Illuminate\Http\Request;

class TripController extends Controller
{
    private $tripRepository;
    public function __construct(TripRepositoryInterface $tripRepository)
    {
        $this->tripRepository = $tripRepository;
    }

    public function searchTrip(Request $request){
        return $this->tripRepository->searchTrip($request->search);
    }

    public function detailForDay($tripId,$dayNum){
        return $this->tripRepository->detailForDay($tripId,$dayNum);
    }
    ///////////////////////////////////////////////////////////////////


}
