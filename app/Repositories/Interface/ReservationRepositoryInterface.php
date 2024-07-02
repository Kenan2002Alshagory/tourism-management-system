<?php

namespace App\Repositories\interface;

interface ReservationRepositoryInterface{
    public function addReservation($request);
    public function cancelledReservation($idReservation);
    public function editReservation($request);
    public function showReservationForMe();
    public function showReservationForTrip($idTrip);
}
