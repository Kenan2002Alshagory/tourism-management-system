<?php

namespace App\Repositories;

use App\Models\Trip;
use App\Models\TripItinerary;
use App\Repositories\interface\TripRepositoryInterface;


class TripRepository implements TripRepositoryInterface{

    public function searchTrip($search){

        if ($search == ''){
        return response()->json([
            'tripType' => [],
            'tripStart_date' => [],
            'tripDestination' => [],
        ], 200);
    }

        $tripType = Trip::where('trip_status','active')->where('status_time','before')->where('trip_type','like','%'.$search.'%')->get();
        $tripStart_date = Trip::where('trip_status','active')->where('status_time','before')->where('start_date','like',$search)->get();
        $tripDestination = Trip::where('trip_status','active')->where('status_time','before')->where('destination','like','%'.$search.'%')->get();

        return response()->json([
            'tripType'=>$tripType,
            'tripStart_date'=>$tripStart_date,
            'tripDestination'=>$tripDestination
        ]);
    }

    public function detailForDay($tripId,$dayNum){
        $details = TripItinerary::where('trip_id',$tripId)->where('day_num',$dayNum)->join('places','places.id','trip_itineraries.place_id')->select('trip_itineraries.id as id','places.id as place_id' , 'places.name as place_name' , 'places.type as place_type' , 'trip_itineraries.description','places.photo_url as place_image')->get();
        return response()->json(['detail'=>$details]);
    }
}
