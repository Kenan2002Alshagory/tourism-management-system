<?php

namespace App\Repositories;

use App\Http\Resources\placeResource;
use App\Http\Resources\tripResource;
use App\Models\Favorite;
use App\Models\Place;
use App\Models\Trip;
use App\Models\User;
use App\Repositories\interface\FavoriteRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class FavoriteRepository implements FavoriteRepositoryInterface{

/////////////////////////Add//////////////////////////////////

    public function addTripToFavorite($id){
        return $this->add('trip',$id);
    }

    public function addPlaceToFavorite($id){
        return $this->add('place',$id);
    }

    protected function add($type,$id){
        if($type == 'place'){
            $favorite = Favorite::where('user_id',Auth::user()->id)
            ->where('favoritable_id',$id)
            ->where('favoritable_type','App\Models\Place')
            ->first();
            if($favorite){
                return response()->json([
                    'message'=>'this place is in favorite'
                ]);
            }
            Place::where('id',$id)->first()->favorites()->create([
                'user_id'=>Auth::user()->id
            ]);
            return response()->json([
                'status'=>true,
                'message'=>'the place is added'
            ]);
        }else{
            $favorite = Favorite::where('user_id',Auth::user()->id)
            ->where('favoritable_id',$id)
            ->where('favoritable_type','App\Models\Trip')
            ->first();
            if($favorite){
                return response()->json([
                    'message'=>'this trip is in favorite'
                ]);
            }
            Trip::where('id',$id)->first()->favorites()->create([
                'user_id'=>Auth::user()->id
            ]);
            return response()->json([
                'status'=>true,
                'message'=>'the trip is added'
            ]);
        }
    }

//////////////////////////////Show/////////////////////////

    public function allFavoriteForMe(){
        return response()->json([
            'favoritesHotel'=>placeResource::collection($this->favoritesPlaces()->where('type','hotel')->get()),
            'favoritesRestaurant'=>placeResource::collection($this->favoritesPlaces()->where('type','restaurant')->get()),
            'favoritesFamous_place'=>placeResource::collection($this->favoritesPlaces()->where('type','famous_place')->get()),
            'favoritesTrip'=>tripResource::collection($this->favoritesTrips()),
        ]);
    }

    protected function favoritesPlaces(){
        return Favorite::where('user_id',Auth::user()->id)
        ->where('favoritable_type', 'App\Models\Place')
        ->join('places','places.id','favorites.favoritable_id')
        ->select('places.*');
    }

    protected function favoritesTrips(){
        return  Favorite::where('favorites.user_id',Auth::user()->id)
        ->where('favoritable_type', 'App\Models\Trip')
        ->join('trips','trips.id','favorites.favoritable_id')
        ->select('trips.*')
        ->get();
    }

///////////////////////////remove/////////////////////////////

    public function removeTripFromFavorite($id){
        return $this->removed('trip',$id);
    }

    public function removePlaceFromFavorite($id){
        return $this->removed('place',$id);
    }

    protected function removed($type,$id){
        if($type == 'place'){
            $favorite = Favorite::where('user_id',Auth::user()->id)
            ->where('favoritable_id',$id)
            ->where('favoritable_type','App\Models\Place')
            ->first();
            if(!$favorite){
                return response()->json([
                    'message'=>'this place is not in favorite'
                ]);
            }
            $favorite->delete();
            return response()->json([
                'status'=>true,
                'message'=>'the place is removed'
            ]);
        }else{
            $favorite = Favorite::where('user_id',Auth::user()->id)
            ->where('favoritable_id',$id)
            ->where('favoritable_type','App\Models\Trip')
            ->first();
            if(!$favorite){
                return response()->json([
                    'message'=>'this trip is not in favorite'
                ]);
            }
            $favorite->delete();
            return response()->json([
                'status'=>true,
                'message'=>'the trip is removed'
            ]);
        }
    }
}
