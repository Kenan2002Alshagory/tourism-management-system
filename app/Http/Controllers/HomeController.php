<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\placeResource;
use App\Http\Resources\tripResource;
use App\Models\Trip;
use App\Models\Place;
use App\Models\TripItinerary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->query('keyword');
    if ($keyword == '')
    return response()->json([
        'hotels' => [],
        'restaurants' => [],
        'famous_places' => [],
        'trips' => [],
    ], 200);

        $placesQuery = Place::query();
        $placesQuery->where(function ($query) use ($keyword) {
            $query->where('name', 'LIKE', "%$keyword%")
                ->orWhere('location', 'LIKE', "%$keyword%")
                ->orWhere('description', 'LIKE', "%$keyword%");
        });
        $hotels = $placesQuery->hotel()->get();

        $placesQuery = Place::query();
        $placesQuery->where(function ($query) use ($keyword) {
            $query->where('name', 'LIKE', "%$keyword%")
                  ->orWhere('location', 'LIKE', "%$keyword%")
                  ->orWhere('description', 'LIKE', "%$keyword%");
        });
        $restaurants = $placesQuery->restaurant()->get();

        $placesQuery = Place::query();
        $placesQuery->where(function ($query) use ($keyword) {
            $query->where('name', 'LIKE', "%$keyword%")
                  ->orWhere('location', 'LIKE', "%$keyword%")
                  ->orWhere('description', 'LIKE', "%$keyword%");
        });
        $famousPlaces = $placesQuery->famous_place()->get();


        $tripsQuery = Trip::query();
        $tripsQuery->where('trip_status', 'pending')->where('status_time','before')->where(function ($query) use ($keyword) {
            $query->where('trip_description', 'LIKE', "%$keyword%")
                  ->orWhere('trip_name', 'LIKE', "%$keyword%")
                  ->orWhere('trip_type', 'LIKE', "%$keyword%")
                  ->orWhere('destination', 'LIKE', "%$keyword%");
        });



        $trips = $tripsQuery->get();

        return response()->json([
            'hotels' => $hotels,
            'restaurants' => $restaurants,
            'famous_places' => $famousPlaces,
            'trips' => $trips,
        ], 200);
    }

     // Show all hotels
    public function showHotels()
    {
        $hotels = Place::where('type', 'hotel')->get();
        return response()->json([
            'data' => placeResource::collection($hotels),
        ], 200);
    }



    // Show all famous places
    public function showFamousPlaces(){
        $famousPlaces = Place::where('type', 'famous_place')->get();
        return response()->json([
            'data' => placeResource::collection($famousPlaces),
        ], 200);
    }

    // Show all restaurants
    public function showRestaurants()
    {
        $restaurants = Place::where('type', 'restaurant')->get();
        return response()->json([
            'data' => placeResource::collection($restaurants),
        ], 200);    }

        // Show details of a specific trip
    public function tripDetails($id)
    {
        $trip = Trip::where('id', $id)->get();
        if (!isset($trip)) {
            return response()->json([
                'error' => 'Trip not found',
            ], 404);
        }
        return response()->json([
            'data' => tripResource::collection($trip),
        ], 200);
    }

        // show details of a place
        public function placeDetails($id)
        {
            $place = Place::where('id', $id)->get();
            if (!isset($place)) {
                return response()->json([
                    'error' => 'Hotel not found',
                ], 404);
            }
            return response()->json([
                'data' => placeResource::collection($place),
            ], 200);
        }

        public function addPlace(Request $request)
        {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:hotel,famous_place,restaurant,activity',
                'location' => 'required|string|max:255',
                'description' => 'required|string',
                'rating' => 'required|numeric|min:0|max:5',
                'photo_url' => 'required',
            ]);

            if ($request->hasFile('photo_url')) {
                $path = $request->file('photo_url')->store('images', ['disk' =>'my_files']);
            }

            $place = Place::create([
                'name' => $request->name,
                'type' => $request->type,
                'location' => $request->location,
                'description' => $request->description,
                'rating' => $request->rating,
                'photo_url' =>$path,
            ]);

            return response()->json(['message' => 'Place added successfully', 'place' => $place]);
        }

        public function editPlace(Request $request, $placeId)
        {
            $place = Place::find($placeId);

            if (!$place) {
                return response()->json(['message' => 'Place not found'], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'type' => 'sometimes|in:hotel,famous_place,restaurant,activity',
                'location' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'rating' => 'sometimes|numeric|min:0|max:5',
                'photo_url' => 'sometimes',
            ]);

            if ($request->hasFile('photo_url')) {
                $path = $request->file('photo_url')->store('images', ['disk' =>'my_files']);
                $place->update(['photo_url'=>$path]);
            }

            $place->update($request->only([
                'name', 'type', 'location', 'description', 'rating'
            ]));

            return response()->json(['message' => 'Place updated successfully', 'place' => $place]);
        }

        public function deletePlace($placeId)
        {
            $place = Place::find($placeId);

            if (!$place) {
                return response()->json(['message' => 'Place not found'], 404);
            }

            $tripItinerary = TripItinerary::where('place_id',$placeId)->first();

            if($tripItinerary){
                return response()->json(['message' => 'you cant delete this place']);
            }

            $place->delete();

            return response()->json(['message' => 'Place deleted successfully']);
        }


}

