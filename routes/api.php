<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripzController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\WalletController;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
/////////////////////auth//////////////////////////////

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/forgetPassword',[AuthController::class,'forgetPassword']);
Route::post('/sendCode',[AuthController::class,'sendCode']);
Route::post('/resetPassword',[AuthController::class,'resetPassword']);

Route::get('/logout',[AuthController::class,'logout'])->middleware('auth:api');
Route::post('/updatePhoto',[AuthController::class,'updatePhoto'])->middleware('auth:api');
Route::post('/updateInfo',[AuthController::class,'updateInfo'])->middleware('auth:api');
Route::get('/userInfo',[AuthController::class,'userInfo'])->middleware('auth:api');
Route::get('/allUser',[AuthController::class,'allUser']);
Route::delete('/deleteUser/{user_id}',[AuthController::class,'deleteUser']);



////////////////////////place method////////////////////////////

////Hotel/////
Route::get('/searchHotel',[PlaceController::class,'searchHotel'])->middleware('auth:api');

////restaurant/////
Route::get('/searchRestaurant',[PlaceController::class,'searchRestaurant'])->middleware('auth:api');

////famous_place/////
Route::get('/searchFamous_place',[PlaceController::class,'searchFamous_place'])->middleware('auth:api');





////////////////trip///////////////////

Route::get('/searchTrip',[TripController::class,'searchTrip'])->middleware('auth:api');
Route::get('/detailForDay/{tripId}/{dayNum}',[TripController::class,'detailForDay'])->middleware('auth:api');



////////////////////favorite////////////////////////

Route::post('/addTripToFavorite/{id}',[FavoriteController::class,'addTripToFavorite'])->middleware('auth:api');
Route::post('/addPlaceToFavorite/{id}',[FavoriteController::class,'addPlaceToFavorite'])->middleware('auth:api');

Route::delete('/removeTripFromFavorite/{id}',[FavoriteController::class,'removeTripFromFavorite'])->middleware('auth:api');
Route::delete('/removePlaceFromFavorite/{id}',[FavoriteController::class,'removePlaceFromFavorite'])->middleware('auth:api');

Route::get('/allFavoriteForMe',[FavoriteController::class,'allFavoriteForMe'])->middleware('auth:api');

/////////////////////////////////////wallet/////////////////////////////////

Route::post('/charging',[WalletController::class,'charging'])->middleware('auth:api','is_user');
Route::delete('/accept/{idOrder}',[WalletController::class,'accept'])->middleware('auth:api','is_admin');
Route::delete('/reject/{idOrder}',[WalletController::class,'reject'])->middleware('auth:api','is_admin');
Route::get('/ViewChargingOrders',[WalletController::class,'ViewChargingOrders'])->middleware('auth:api','is_admin');


////////////////////////////////reservation/////////////////////////////////////////////////
Route::post('/addReservation',[ReservationController::class,'addReservation'])->middleware('auth:api','is_user');
Route::post('/cancelledReservation/{reservationId}',[ReservationController::class,'cancelledReservation'])->middleware('auth:api','is_user');
Route::post('/editReservation',[ReservationController::class,'editReservation'])->middleware('auth:api','is_user');
Route::get('/showReservationForMe',[ReservationController::class,'showReservationForMe'])->middleware('auth:api','is_user');
Route::get('/showReservationForTrip/{tripId}',[ReservationController::class,'showReservationForTrip'])->middleware('auth:api');


Route::middleware('auth:api')->group(function () {
    Route::post('/trips/{tripId}/approve', [TripzController::class, 'approveTrip'])->middleware('is_admin');
    Route::post('/trips/{tripId}/reject', [TripzController::class, 'rejectTrip'])->middleware('is_admin');
    /////////////////trips
    Route::post('/trips', [TripzController::class, 'createTrip']);
    Route::post('/FCreateTrips', [TripzController::class, 'FcreateTrip']);
    Route::post('/SCreateTrips', [TripzController::class, 'ScreateTrip']);
    Route::get('/placesForCreate', [TripzController::class, 'placesForCreate']);
    Route::post('/trips/{tripId}', [TripzController::class, 'updateTrip']);
    Route::delete('/trips/{tripId}', [TripzController::class, 'deleteTrip']);
    /////////////////////trips itinerary
    Route::post('/trips/{tripId}/itinerary' , [TripzController::class, 'addItinerary']);
    Route::post('/trips/{tripId}/itinerary/{dayNum}' , [TripzController::class, 'updateItinerary']);
    Route::delete('/trips/{tripId}/itinerary/{dayNum}' , [TripzController::class, 'deleteItinerary']);
    ////////////////////place itinerary
    Route::post('/trips/{tripId}/itinerary/day/{dayNum}' , [TripzController::class, 'addPlaceToItinerary']);
    Route::post('/trips/itinerary/day/{itineraryId}' , [TripzController::class, 'updatePlaceFromItinerary']);
    Route::delete('/trips/{tripId}/itinerary/day/{dayNum}/place/{placeId}' , [TripzController::class, 'deletePlaceFromItinerary']);
});

Route::get('/trips/active', [TripzController::class, 'getAllActiveTrips'])->middleware('auth:api');
Route::get('/trips/pending', [TripzController::class, 'getAllPendingTrips']);
Route::get('/getMyTrip', [TripzController::class, 'getMyTrip'])->middleware('auth:api');

Route::controller(HomeController::class)->group(function () {
    Route::get('trips', 'allTrips')->middleware('auth:api');
    Route::get('hotels', 'showHotels')->middleware('auth:api');
    Route::get('rest', 'showRestaurants')->middleware('auth:api');
    Route::get('fam', 'showFamousPlaces')->middleware('auth:api');
    Route::get('tripdetail/{id}/description', 'tripDetails')->middleware('auth:api');
    Route::get('placedetail/{id}/description', 'placeDetails')->middleware('auth:api');
    Route::get('homesearch', 'search');
});



Route::post('/places', [HomeController::class, 'addPlace'])->middleware('auth:api', 'is_admin');
Route::post('/places/{placeId}', [HomeController::class, 'editPlace'])->middleware('auth:api', 'is_admin');
Route::delete('/places/{placeId}', [HomeController::class, 'deletePlace'])->middleware('auth:api', 'is_admin');

Route::post('/trips/{tripId}/reviews', [ReviewsController::class, 'createReview'])->middleware('auth:api');
Route::get('/trips/{tripId}/reviews', [ReviewsController::class, 'getReviews'])->middleware('auth:api');
Route::delete('/reviews/{reviewId}', [ReviewsController::class, 'deleteReview'])->middleware('auth:api');


