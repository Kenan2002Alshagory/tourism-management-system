<?php

namespace App\Repositories;

use App\Models\Reservation;
use App\Models\Trip;
use App\Models\Wallet;
use App\Repositories\interface\ReservationRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationRepository implements ReservationRepositoryInterface{

    public function addReservation($request){
        $data=$request->all();
        $userId = Auth::user()->id;
        $wallet = Wallet::where('user_id',$userId)->first();
        $trip = Trip::where('trip_status','active')->where('status_time','before')->where('id',$data['tripId'])->first();

        if($data['payment_status'] == 'paid'){
            if(!$this->enouphBalance($wallet,$trip,$data['travelers_num'])){
                return response()->json([
                    'message'=>'there are no enouph balance in your wallet'
                ]);
            }
        }

        if(!$this->enouphPlace($trip,$data['travelers_num'])){
            return response()->json([
                'message'=>'there are no enouph place'
            ]);
        }
        $allPlace = $trip->travelers_num;
        $AllPrice = $trip->trip_price * $data['travelers_num'];
        $walletBalance = $wallet->balance;
        $walletTrip = Wallet::where('user_id',$trip->user_id)->first();
        $walletTripBalance = $walletTrip->balance;
        if($allPlace > $data['travelers_num']){
            $trip->update([
                'travelers_num'=>$allPlace - $data['travelers_num'],
            ]);
        }else if($allPlace = $data['travelers_num']){
            $trip->update([
                'travelers_num'=>$allPlace -$data['travelers_num'],
                'trip_status'=>'completed'
            ]);
        }
        if($data['payment_status'] == 'paid'){
            $wallet->update([
                'balance'=>$walletBalance-$AllPrice
            ]);
            $walletTrip->update([
                'balance'=>$walletTripBalance+$AllPrice
            ]);
        }

        Reservation::create([
            'user_id'=>$userId,
            'trip_id'=>$data['tripId'],
            'travelers_num'=>$data['travelers_num'],
            'total'=>$AllPrice,
            'res_status'=>'booked',
            'res_date'=>Carbon::today()->format('Y-m-d'),
            'payment_status'=>$data['payment_status'],
        ]);

        return response()->json([
            'message'=>'your reservation created'
        ]);
    }

    protected function enouphBalance($wallet,$trip,$numTrav){
        $AllPrice = $trip->trip_price * $numTrav;
        $walletBalance = $wallet->balance;
        if($walletBalance >= $AllPrice){
            return true;
        }
        return false;
    }

    protected function enouphPlace($trip,$numTrav){
        $allPlace = $trip->travelers_num;
        if($allPlace >= $numTrav){
            return true;
        }
        return false;
    }

    public function cancelledReservation($idReservation){

        $reservation = Reservation::where('id',$idReservation)->first();
        $trip = Trip::where('id',$reservation->trip_id)->first();
        if($trip->status_time != 'before'){
            return response()->json([
                'message'=>'you can not delete this reservation becaouse the trip is starting or ending'
            ]);
        }
        if($reservation->payment_status == 'paid'){
            $this->retriveMony($reservation,$trip);
        }

        if($trip->trip_status == 'completed'){
            $trip->update([
                'travelers_num' =>$reservation->travelers_num,
                'trip_status' => 'active'
            ]);
        }else{
            $tripTravNum = $trip->travelers_num;
            $trip->update([
                'travelers_num' => $tripTravNum + $reservation->travelers_num,
            ]);
        }

        $reservation->update([
            'res_status'=>'cancelled'
        ]);

        return response()->json([
            'message'=>'your rsesrvation cancelled'
        ]);

    }

    protected function retriveMony($reservation,$trip){
        $wallet = Wallet::where('user_id',$reservation->user_id)->first();
        $walletBalance = $wallet->balance;
        $wallet->update([
            'balance' => $walletBalance+$reservation->total,
        ]);
        $walletTrip = Wallet::where('user_id',$trip->user_id)->first();
        $walletTripBalance = $walletTrip->balance;
        $walletTrip->update([
            'balance' => $walletTripBalance-$reservation->total,
        ]);
    }

    public function editReservation($request){
        $data = $request->all();
        $reservation = Reservation::where('id',$data['reservationId'])->first();
        if($data['travelers_num'] == $reservation->travelers_num){
            return response()->json([
                'message'=>'you already has same travelers_number'
            ]);
        }
        $trip = Trip::where('id',$reservation->trip_id)->first();
        if($trip->status_time != 'before'){
            return response()->json([
                'message'=>'you can not edit this reservation becaouse the trip is starting or ending'
            ]);
        }
        $difTrav = $data['travelers_num'] - $reservation->travelers_num;
        $tripNewTravNum = $trip->travelers_num - $difTrav;
        if($tripNewTravNum < 0){
            return response()->json([
                'message'=>'there are no enouph place for edit'
            ]);
        }
        $wallet = Wallet::where('user_id',$reservation->user_id)->first();
        $walletBalance = $wallet->balance;
        $walletTrip = Wallet::where('user_id',$trip->user_id)->first();
        $walletTripBalance = $walletTrip->balance;
        $difPrice = $trip->trip_price * $difTrav;
        if($reservation->payment_status == 'paid'){
            if($difTrav > 0 && $walletBalance < $difPrice){
                    return response()->json([
                        'message'=>'there are no enouph balance in your wallet'
                    ]);
            }else{
                $wallet->update([
                    'balance' => $walletBalance - $difPrice
                ]);
                $walletTrip->update([
                    'balance' => $walletTripBalance + $difPrice,
                ]);
            }
        }
        if($trip->trip_status == 'completed'){
            $trip->update([
                'travelers_num' =>$tripNewTravNum,
                'trip_status' => 'active'
            ]);
        }else{
            $trip->update([
                'travelers_num' => $tripNewTravNum,
            ]);
        }
        $reservation->update([
            'travelers_num'=>$data['travelers_num'],
            'total'=>$data['travelers_num'] * $trip->trip_price
        ]);
        return response()->json([
            'message'=>'your reservation edited'
        ]);
    }

    public function showReservationForMe(){
        $newReservation = $this->myReservations()->where('trips.status_time','before')->get();
        $oldReservation = $this->myReservations()->where('trips.status_time','!=','before')->get();
        return response()->json([
            'newReservation'=>$newReservation,
            'oldReservation'=>$oldReservation
        ]);
    }

    protected function myReservations(){
        return DB::table('reservations')->where('res_status','booked')
        ->where('reservations.user_id',Auth::user()->id)
        ->join('trips','trips.id','reservations.trip_id')
        ->select('reservations.id as reserv_id','trips.id as trip_id','trips.status_time','trips.trip_status','reservations.travelers_num as reserTravNum' , 'trips.travelers_num as tripTravNum','reservations.total','reservations.res_date','reservations.payment_status','trips.start_date');
    }

    public function showReservationForTrip($idTrip){
        $bookedReservations = $this->tripReservations($idTrip)->where('res_status','booked')->get();
        $cancelledReservations = $this->tripReservations($idTrip)->where('res_status','cancelled')->get();
        return response()->json([
            'bookedReservations'=>$bookedReservations,
            'cancelledReservations'=>$cancelledReservations
        ]);
    }

    protected function tripReservations($idTrip){
        return DB::table('reservations')
        ->where('reservations.trip_id',$idTrip)
        ->join('users','users.id','reservations.user_id')
        ->select('reservations.id as reserv_id','reservations.trip_id as trip_id','users.id as user_id','users.photo_path','users.username as user_name','reservations.travelers_num as reserTravNum' , 'reservations.total','reservations.res_date','reservations.payment_status','reservations.res_status');
    }

}
