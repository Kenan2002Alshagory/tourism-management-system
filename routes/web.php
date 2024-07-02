<?php

use App\Http\Controllers\PlaceController;
use App\Http\Controllers\TripController;
use App\Mail\forgetPassMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/',function(){
    $token = rand(10000,99999);
    Mail::to('kalshagory@gmail.com')->send(new forgetPassMail($token));
    return true;
});

// Route::get('/testMail',function(){
//     $token = rand(10000,99999);
//     Mail::to('kalshagory@gmail.com')->send(new forgetPassMail($token));
//     return true;
// });
