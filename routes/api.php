<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::GET('importdemodata', [BookingController::class, "importDemoData"]);
Route::GET('auth', [BookingController::class, "auth"]);

Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::POST('book', [BookingController::class, "bookSlots"]);
    Route::POST('slotcheckin', [BookingController::class, "slotCheckin"]);
    Route::POST('slotcheckout', [BookingController::class, "slotCheckout"]);
    Route::GET('getallavailableslot', [BookingController::class, "getAllAvailableSlot"]);
    Route::GET('getalloccupiedslot', [BookingController::class, "getAllOccupiedSlot"]);
    Route::GET('getallusers', [BookingController::class, "getAllUsers"]);
});

 

 
