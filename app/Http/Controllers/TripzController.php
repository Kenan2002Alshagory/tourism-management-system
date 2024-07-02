<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\User;
use App\Models\TripItinerary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateTripRequest;
use App\Http\Requests\FCreateTripRequest;
use App\Http\Requests\SCreateTripRequest;
use App\Http\Resources\tripResource;
use App\Models\Place;
use App\Models\Reservation;
use App\Repositories\interface\ReservationRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TripzController extends Controller
{
    private $reservationRepository;

    public function __construct(ReservationRepositoryInterface $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    public function approveTrip($tripId)
    {

        $trip = Trip::find($tripId);

        if (!$trip) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        if ($trip->trip_status === 'active') {
            return response()->json(['message' => 'Trip is already active and cannot be approved again'], 400);
        }

        $trip->trip_status = 'active';
        $trip->save();

        return response()->json(['message' => 'Trip approved successfully', 'trip' => $trip]);
    }

    public function rejectTrip($tripId)
    {

        $trip = Trip::find($tripId);

        if (!$trip) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        if ($trip->trip_status === 'active') {
            return response()->json(['message' => 'Trip is active and cannot be rejected'], 400);
        }

        $trip->delete();

        return response()->json(['message' => 'Trip rejected and deleted successfully']);
    }


    public function getAllActiveTrips()
    {
        $trips = Trip::where('trip_status', 'active')->where('status_time','before')->get();
        return response()->json(['trips' => tripResource::collection($trips)],200);
    }

    public function getAllPendingTrips()
    {
        $trips = Trip::where('trip_status', 'pending')->where('status_time','before')->get();
        return response()->json(['trips' => $trips],200);
    }

    public function getMyTrip(){
        return response()->json([
            'MyNewTrips'=>Trip::where('user_id',Auth::user()->id)->where('trip_status','!=','without_details')->where('start_date','>',Carbon::now())->get(),
            'MyOldTrips'=>Trip::where('user_id',Auth::user()->id)->where('trip_status','!=','without_details')->where('start_date','<=',Carbon::now())->get(),
        ]);
    }

    public function createTrip(CreateTripRequest $request)
    {
        $user = Auth::user();
        $trip_status = $user->role === 'admin' ? 'active' : 'pending';

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $duration = $end->diffInDays($start) + 1;
        if($duration != $request->duration){
            return response()->json([
                'message'=>'Duration is not equal different between start and end date '.$duration.''
            ]);
        }

    $dayNumbers = array_column($request->itineraries, 'day_num');
    $dayNumbers = array_map(function($value) {
        return intval($value);
    }, $dayNumbers);
    sort($dayNumbers);
    if ($dayNumbers !== range(1, count($dayNumbers))) {
        return response()->json([
            'message' => 'Day numbers are not in sequential order or do not start from 1.'
        ], 422);
    }

    if(count($dayNumbers) !=  $request->duration){
        return response()->json(['message'=>'you have some days without activities please enter some activities']);
    }

    foreach ($request->itineraries as $itinerary) {
        $placeIds = array_column($itinerary['places'], 'place_id');
        if (count($placeIds) !== count(array_unique($placeIds))) {
            return response()->json([
                'message' => 'Duplicate places found in day ' . $itinerary['day_num']
            ], 422);
        }
    }
    if ($request->trip_price < 0) {
        return response()->json([
            'message' => 'Trip price must be a positive value.'
        ], 422);
    }

    if ($request->hasFile('trip_image')) {
        $path = $request->file('trip_image')->store('images', ['disk' =>'my_files']);
    }
        DB::beginTransaction();
        try {
            $trip = Trip::create([
                'user_id' => $user->id,
                'trip_name' => $request->trip_name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'duration' => $request->duration,
                'status_time' => 'before',
                'destination' => $request->destination,
                'from' => $request->from,
                'guide_name' => $request->guide_name,
                'travelers_num' => $request->travelers_num,
                'trip_type' => $request->trip_type,
                'trip_price' => $request->trip_price,
                'trip_status' => $trip_status,
                'trip_description' => $request->trip_description,
                'trip_image' => $path,
            ]);

            foreach ($request->itineraries as $itinerary) {
                foreach ($itinerary['places'] as $place) {
                    if (!Place::where('id', $place['place_id'])->exists()) {
                        DB::rollback();
                        return response()->json([
                            'message' => 'Place with id ' . $place['place_id'] . ' does not exist.'
                        ], 422);
                    }
                    TripItinerary::create([
                        'trip_id' => $trip->id,
                        'place_id' => $place['place_id'],
                        'day_num' => $itinerary['day_num'],
                        'description' => $place['description'],
                    ]);
                }
            }

            DB::commit();

            $message = $user->role === 'admin' ? 'Trip created successfully and is active' : 'Trip created successfully and is pending approval';
        return response()->json(['message' => $message, 'trip' => $trip]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Failed to create trip', 'error' => $e->getMessage()], 500);
        }
    }

    public function FcreateTrip(FCreateTripRequest $request){

        $user = Auth::user();
        $trip_status = $user->role === 'admin' ? 'active' : 'pending';

        if ($request->trip_price < 0) {
            return response()->json([
                'message' => 'Trip price must be a positive value.'
            ], 422);
        }

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $duration = $end->diffInDays($start) + 1;
        if($duration != $request->duration){
            return response()->json([
                'message'=>'Duration is not equal different between start and end date '.$duration.''
            ]);
        }

        // if ($request->hasFile('trip_image')) {
        //     $path = $request->file('trip_image')->store('images', ['disk' =>'my_files']);
        // }

        $trip = Trip::create([
            'user_id' => $user->id,
            'trip_name' => $request->trip_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration' => $request->duration,
            'status_time' => 'before',
            'destination' => $request->destination,
            'from' => $request->from,
            'guide_name' => $request->guide_name,
            'travelers_num' => $request->travelers_num,
            'trip_type' => $request->trip_type,
            'trip_price' => $request->trip_price,
            'trip_status' => "without_details",
            'trip_description' => $request->trip_description,
            'trip_image' => $request->trip_image,
            // 'trip_image' => $path,
        ]);

        return response()->json([
            'message'=>'pleace enter your activites',
            'trip_id'=>$trip->id,
            'duration'=>$request->duration
        ]);
    }

    public function ScreateTrip(SCreateTripRequest $request){

        $user = Auth::user();
        $trip = Trip::where("id",$request->trip_id)->first();

        if($user->id != $trip->user_id){
            return response()->json([
                "message"=>"this in not your trip"
            ]);
        }
        $trip_status = $user->role === 'admin' ? 'active' : 'pending';

        $dayNumbers = array_column($request->itineraries, 'day_num');
        $dayNumbers = array_map(function($value) {
            return intval($value);
        }, $dayNumbers);
        sort($dayNumbers);
        if ($dayNumbers !== range(1, count($dayNumbers))) {
            return response()->json([
                'message' => 'Day numbers are not in sequential order or do not start from 1.'
            ], 422);
        }

        if(count($dayNumbers) !=  $trip->duration){
            return response()->json(['message'=>'you have some days without activities please enter some activities']);
        }

        foreach ($request->itineraries as $itinerary) {
            $placeIds = array_column($itinerary['places'], 'place_id');
            if (count($placeIds) !== count(array_unique($placeIds))) {
                return response()->json([
                    'message' => 'Duplicate places found in day ' . $itinerary['day_num']
                ], 422);
            }
        }

        DB::beginTransaction();
            try {
            foreach ($request->itineraries as $itinerary) {
                foreach ($itinerary['places'] as $place) {
                    if (!Place::where('id', $place['place_id'])->exists()) {
                        DB::rollback();
                        return response()->json([
                            'message' => 'Place with id ' . $place['place_id'] . ' does not exist.'
                        ], 422);
                    }
                    TripItinerary::create([
                        'trip_id' => $trip->id,
                        'place_id' => $place['place_id'],
                        'day_num' => $itinerary['day_num'],
                        'description' => $place['description'],
                    ]);
                }
            }
            $trip->update([
                'trip_status'=>$trip_status
            ]);

            DB::commit();

                $message = $user->role === 'admin' ? 'Trip created successfully and is active' : 'Trip created successfully and is pending approval';
            return response()->json(['message' => $message, 'trip' => $trip]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['message' => 'Failed to create trip', 'error' => $e->getMessage()], 500);
            }
    }

    public function placesForCreate(){
        return response()->json([
            'places' => Place::all(),
        ]);
    }

    public function updateTrip(Request $request, $tripId)
    {
        $user = Auth::user();
        $trip = Trip::find($tripId);

        if (!$trip) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        if ($user->id !== $trip->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if($trip->status_time != 'before'){
            return response()->json(['message' => 'this trip is started you cant update this trip']);
        }

        if ($request->has('start_date')){
            if(!$request->has('end_date')){
            return response()->json(['message' => 'you can not update start date without end date']);
            }
        }
        if ($request->has('end_date')){
            if(!$request->has('start_date')){
            return response()->json(['message' => 'you can not update end date without end date']);
            }
        }
        $validated = $request->validate([
            'trip_name' => 'sometimes|string',
            'start_date' => 'sometimes|date|before:end_date|after:today',
            'end_date' => 'sometimes|date|after:today',
            'from' => 'sometimes|string',
            'destination' => 'sometimes|string',
            'guide_name' => 'sometimes|string',
            'trip_type' => 'sometimes|string',
            'travelers_num' => 'sometimes|integer|min:1',
            'trip_price' => 'sometimes|numeric|min:0',
            'trip_description' => 'sometimes|string',
            'trip_image' => 'sometimes'
        ]);

        // Check if start_date and end_date are provided and ensure duration doesn't change
        if ($request->has('start_date') && $request->has('end_date')) {
            $newStartDate = Carbon::parse($request->start_date);
            $newEndDate = Carbon::parse($request->end_date);
            $newDuration = $newEndDate->diffInDays($newStartDate) + 1;
            if ($newDuration != $trip->duration) {
                return response()->json(['error' => 'Duration cannot change when modifying start_date and end_date.'], 422);
            }
        }

        if ($request->hasFile('trip_image')) {
            $path = $request->file('trip_image')->store('images', ['disk' =>'my_files']);
            $trip->update(['trip_image'=>$path]);
        }

        // Update trip details
        $trip->update($request->only([
            'trip_name', 'start_date', 'end_date', 'from', 'destination',
            'guide_name', 'travelers_num', 'trip_type', 'trip_price',
            'trip_description'
        ]));

        return response()->json(['message' => 'Trip updated successfully', 'trip' => $trip]);
    }

    public function deleteTrip($tripId){
        $user = Auth::user();
        $trip = Trip::find($tripId);

        if (!$trip) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        if ($user->id !== $trip->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if($trip->status_time != 'before'){
            return response()->json(['message' => 'this trip is started you cant delete it this trip']);
        }

        if($trip->trip_status == 'pending'){
            $itineraryTrip = TripItinerary::where('trip_id',$tripId)->delete();
            $trip->delete();
            return response()->json(['message' => 'this trip is deleted']);
        }

        $reservations = Reservation::where('trip_id',$tripId)->get();
        if($reservations){
            foreach($reservations as $reservation){
                $this->reservationRepository->cancelledReservation($reservation->id);
            }
        }
        $itineraryTrip = TripItinerary::where('trip_id',$tripId)->delete();
        $trip->delete();
        return response()->json(['message' => 'this trip is deleted']);
    }

    public function addItinerary(Request $request, $tripId)
    {
        $trip = Trip::findOrFail($tripId);

        if($trip->status_time != 'before'){
            return response()->json(['message' => 'this trip is started you cant added itinery']);
        }

        if (auth()->user()->id != $trip->user_id) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $maxDayNum = $trip->duration; // Current maximum day number
        $expectedNextDay = $maxDayNum + 1; // Expected next day

        $validator = $request->validate([
            'itineraries' => 'required|array',
            'itineraries.*.places' => 'required|array',
            'itineraries.*.places.*.place_id' => 'required|exists:places,id',
            'itineraries.*.places.*.description' => 'sometimes|string',
            'itineraries.*.day_num' => 'required|integer|min:'.$expectedNextDay,
        ]);

        DB::beginTransaction();
        try {
        foreach ($request->itineraries as $itinerary) {
            $placeIds = array_column($itinerary['places'], 'place_id');
            if (count($placeIds) !== count(array_unique($placeIds))) {
                DB::rollback();
                return response()->json([
                    'message' => 'Duplicate places found in day ' . $itinerary['day_num']
                ], 422);
            }
            if ($itinerary['day_num'] !== $expectedNextDay) {
                DB::rollback();
                return response()->json(['error' => 'Days must be added sequentially. Expected day ' . $expectedNextDay . '.'], 422);
            }
            foreach ($itinerary['places'] as $place) {
                TripItinerary::create([
                    'trip_id' => $trip->id,
                    'place_id' => $place['place_id'],
                    'day_num' => $itinerary['day_num'],
                    'description' => $place['description'],
                ]);
            }
            $expectedNextDay++;
        }
        // Update trip duration and dates
        $trip->duration = $expectedNextDay - 1;
        $trip->end_date = Carbon::parse($trip->start_date)->addDays($trip->duration - 1)->format('Y-m-d');
        $trip->save();
        DB::commit();
        return response()->json(['message' => 'Itinerary added successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Failed to create trip', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateItinerary(Request $request,$tripId, $dayNum){
        $trip = Trip::findOrFail($tripId);

        if($trip->status_time != 'before'){
            return response()->json(['message' => 'this trip is started you cant updated Itinerary']);
        }

        if (auth()->user()->id != $trip->user_id) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $validator = $request->validate([
            'places' => 'required|array',
            'places.*.place_id' => 'required|exists:places,id',
            'places.*.description' => 'required|string',
        ]);

        $placeIds = array_column($request->places, 'place_id');
        if (count($placeIds) !== count(array_unique($placeIds))) {
            DB::rollback();
            return response()->json([
                'message' => 'Duplicate places found'
            ]);
        }

        $itineraries = TripItinerary::where('trip_id', $tripId)->where('day_num', $dayNum)->delete();

        foreach ($request->places as $place) {
            TripItinerary::create([
                'trip_id' => $trip->id,
                'place_id' => $place['place_id'],
                'day_num' => $dayNum,
                'description' => $place['description'],
            ]);
        }

        return response()->json([
            'message'=>'your itinerary updated'
        ]);

    }

    public function deleteItinerary($tripId, $dayNum){
        $trip = Trip::findOrFail($tripId);

        if($trip->status_time != 'before'){
            return response()->json(['message' => 'this trip is started you cant delete Itinerary']);
        }

        if (auth()->user()->id != $trip->user_id) {
            return response()->json(['error' => 'Unauthorized action.']);
        }

        if($trip->duration == 1){
            return response()->json(['message' => 'you cant delete this day because you have only this day']);
        }

        $itineraries = TripItinerary::where('trip_id', $tripId)->where('day_num', $dayNum)->delete();

        if($dayNum != $trip->duration){
            $itineraries = TripItinerary::where('trip_id', $tripId)->where('day_num','>', $dayNum)->get();
            foreach($itineraries as $itinerarie){
                $dayNum = $itinerarie->day_num;
                $itinerarie->day_num = $dayNum - 1;
                $itinerarie->save();
            }
        }

        $duration = $trip->duration;
        $trip->duration = $duration - 1;
        $date = $trip->end_date;
        $new_date = date('Y-m-d', strtotime('-1 day', strtotime($date)));
        $trip->end_date = $new_date;
        $trip->save();
        return response()->json(['message'=>'your Itinerary deleted']);
    }

    public function addPlaceToItinerary($tripId, $dayNum,Request $request){
        $trip = Trip::findOrFail($tripId);

        if($trip->status_time != 'before'){
            return response()->json(['message' => 'this trip is started you cant add place to Itinerary']);
        }

        if (auth()->user()->id != $trip->user_id) {
            return response()->json(['error' => 'Unauthorized action.']);
        }

        if($dayNum > $trip->duration){
            return response()->json(['error' => 'this day is not valid']);
        }

        $validator = $request->validate([
            'place_id' => 'required|exists:places,id',
            'description' => 'required|string',
        ]);

        $itinerarie = TripItinerary::where('trip_id', $tripId)->where('day_num', $dayNum)->where('place_id',$request->place_id)->first();
        if($itinerarie){
            return response()->json(['message'=>'this place is already exist in this day']);
        }

        TripItinerary::create([
            'trip_id' => $trip->id,
            'place_id' => $request->place_id,
            'day_num' => $dayNum,
            'description' => $request->description,
        ]);

        return response()->json(['message'=>'your place is added to itinerary']);
    }

    public function updatePlaceFromItinerary($itineraryId,Request $request){
        $itinerary = TripItinerary::where('id', $itineraryId)->first();

        if(!$itinerary){
            return response()->json(['message'=>'this place is not found']);
        }

        $trip = Trip::findOrFail($itinerary->trip_id);

        if($trip->status_time != 'before'){
            return response()->json(['message' => 'this trip is started you cant update place from Itinerary']);
        }

        if (auth()->user()->id != $trip->user_id) {
            return response()->json(['error' => 'Unauthorized action.']);
        }

        $validator = $request->validate([
            'place_id' => 'required|exists:places,id',
            'description' => 'required|string',
        ]);

        TripItinerary::create([
            'trip_id' => $trip->id,
            'place_id' => $request->place_id,
            'day_num' => $itinerary->day_num,
            'description' => $request->description,
        ]);
        $itinerary->delete();
        return response()->json(['message' => 'Place updated successfully']);

    }

    public function deletePlaceFromItinerary($tripId, $dayNum, $placeId)
    {
        $trip = Trip::findOrFail($tripId);

        if($trip->status_time != 'before'){
            return response()->json(['message' => 'this trip is started you cant delete place from Itinerary']);
        }

        if (auth()->user()->id != $trip->user_id) {
            return response()->json(['error' => 'Unauthorized action.']);
        }

        $itineraries = TripItinerary::where('trip_id', $tripId)->where('day_num', $dayNum)->get();

        if ($itineraries->isEmpty()) {
            return response()->json(['error' => 'No itinerary found for the given day number.'], 404);
        }

        if ($itineraries->count() === 1) {
            return response()->json(['error' => 'Cannot delete the only place for this day.'], 422);
        }
        $itinerary = $itineraries->firstWhere('place_id', $placeId);

        if (!$itinerary) {
            return response()->json(['error' => 'Place not found in the itinerary.'], 404);
        }

        $itinerary->delete();
        return response()->json(['message' => 'Place deleted successfully']);
    }
}
