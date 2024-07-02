<?php

namespace App\Http\Controllers;

use App\Http\Requests\addReservationRequest;
use App\Http\Requests\editReservationRequest;
use App\Repositories\interface\ReservationRepositoryInterface;
use Illuminate\Http\Request;

class ReservationController extends Controller
{

    private $reservationRepository;

    public function __construct(ReservationRepositoryInterface $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    public function addReservation(addReservationRequest $resquest){
        return $this->reservationRepository->addReservation($resquest);
    }

    public function cancelledReservation($idReservation){
        return $this->reservationRepository->cancelledReservation($idReservation);
    }

    public function editReservation(editReservationRequest $request){
        return $this->reservationRepository->editReservation($request);
    }

    public function showReservationForMe(){
        return $this->reservationRepository->showReservationForMe();
    }

    public function showReservationForTrip($idTrip){
        return $this->reservationRepository->showReservationForTrip($idTrip);
    }

}
