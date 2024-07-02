<?php

namespace App\Repositories;

use App\Models\Place;
use App\Repositories\interface\PlaceRepositoryInterface;

class PlaceRepository implements PlaceRepositoryInterface{

    public function searchHotel($search){

        if ($search == '')
        return response()->json([
            'hotelName' => [],
            'hotelLocation' => [],
        ], 200);

        $hotelName = $this->search('hotel','name',$search);
        $hotelLocation = $this->search('hotel','location',$search);

        return response()->json([
            'hotelName'=>$hotelName,
            'hotelLocation'=>$hotelLocation
        ]);
    }

    public function searchRestaurant($search){

    if ($search == ''){
        return response()->json([
            'restaurantName' => [],
            'restaurantLocation' => [],
            'restaurantFoodType' => [],
        ], 200);
    }

        $restaurantName = $this->search('restaurant','name',$search);
        $restaurantLocation =  $this->search('restaurant','location',$search);
        $restaurantFoodType =  $this->search('restaurant','description',$search);

        return response()->json([
            'restaurantName'=>$restaurantName,
            'restaurantLocation'=>$restaurantLocation,
            'restaurantFoodType'=>$restaurantFoodType
        ]);
    }

    public function searchFamous_place($search){

        if ($search == ''){
        return response()->json([
            'famous_placeName' => [],
            'famous_placeLocation' => [],
        ], 200);
        }
        $famous_placeName = $this->search('famuos_place','name',$search);
        $famous_placeLocation = $this->search('famuos_place','location',$search);

        return response()->json([
            'famous_placeName'=>$famous_placeName,
            'famous_placeLocation'=>$famous_placeLocation
        ]);
    }

    protected function search($type,$para,$search){
        if($type == 'hotel'){
            return Place::hotel()->where($para,'like','%'.$search.'%')->get();
        }else if($type == 'restaurant'){
            return Place::restaurant()->where($para,'like','%'.$search.'%')->get();
        }else{
            return Place::famous_place()->where($para,'like','%'.$search.'%')->get();
        }
    }


}


