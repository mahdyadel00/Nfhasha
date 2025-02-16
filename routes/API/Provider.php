<?php

use App\Http\Controllers\API\Provider\AccountController;
use App\Http\Controllers\API\Provider\OfferController;
use App\Http\Controllers\API\Provider\OrderController;
use App\Http\Controllers\API\User\{AppController, ContactUsController};
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
Route::group(['prefix' => 'app' , 'controller' =>AppController::class], function () {
    Route::get('terms-and-conditions', 'terms');
    Route::get('privacy-and-policy', 'privacy');
    Route::get('about-us', 'about');
Route::get('faq' , 'faq')->name('faq');

});

Route::post('change-password', [AccountController::class, 'changePassword']);
Route::get('logout', [AccountController::class, 'logout']);
Route::delete('delete-account', [AccountController::class, 'deleteAccount']);
Route::post('update-profile', [AccountController::class, 'updateProfile']);
Route::get('profile', [AccountController::class, 'profile']);
Route::post('update-geos' , [AccountController::class , 'updateGeos']);


//get all express services
Route::get('express-services' , [AppController::class , 'expressServices']);



// Route::group(['prefix' => 'wallet' , 'controller' => WalletController::class] , function () {
//     Route::get('', 'index');
//     Route::post('deposit', 'deposit');
//     Route::post('withdraw', 'withdraw');
// });


Route::post('contact-us' , ContactUsController::class)->name('contact-us');

// get all offers
Route::get('offers' , [OfferController::class , 'offers']);
Route::get('offer/{id}' , [OfferController::class , 'offer']);
Route::post('accept-offer/{id}' , [OfferController::class , 'acceptOffer']);
Route::post('reject-offer/{id}' , [OfferController::class , 'rejectOffer']);
Route::post('send-offer/{id}' , [OfferController::class , 'sendOffer']);
Route::post('complete-offer/{id}' , [OfferController::class , 'completeOffer']);

//get my orders
Route::get('my-orders' , [OrderController::class , 'myOrders']);
Route::get('order/{id}' , [OrderController::class , 'show']);
Route::get('orders' , [OrderController::class , 'ordersByStatus']);
Route::post('change-order-status/{id}' , [OrderController::class , 'changeOrderStatus']);
