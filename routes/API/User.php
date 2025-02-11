<?php

use App\Http\Controllers\API\User\{AppController,
    ContactUsController,
    ExpressServiceController,
    NotificationController,
    UserController,
    VehiclesController,
    VehiclesInfoController,
    WalletController,
    ProviderController};
use App\Http\Controllers\API\User\OrderController;
use App\Http\Resources\API\CyPeriodicResource;
use App\Models\CyPeriodic;
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

Route::get('' , function () {
    return response()->json([
        'data' => auth()->user()
    ]);
});


Route::group(['prefix' => 'vehicles-info' , 'controller' =>VehiclesInfoController::class], function () {
    Route::get('years', 'years');
    Route::get('types', 'types');
    Route::get('brands', 'brands');
    Route::get('models', 'models');
});

Route::group(['prefix' => 'vehicles' , 'controller' =>VehiclesController::class], function () {
    Route::get('', 'index');
    Route::post('', 'store');
    Route::get('{vehicle}', 'show');
    Route::post('{vehicle}', 'update');
    Route::delete('{vehicle}', 'destroy');
});

Route::post('change-password', [UserController::class, 'changePassword']);
Route::get('notifications', [UserController::class, 'notifications']);
Route::get('notifications/{notification}', [UserController::class, 'notification']);
Route::get('logout', [UserController::class, 'logout']);
Route::delete('delete-account', [UserController::class, 'deleteAccount']);
Route::post('update-profile', [UserController::class, 'updateProfile']);
Route::post('update-geos' , [UserController::class , 'updateGeos']);

Route::group(['prefix' => 'app' , 'controller' =>AppController::class], function () {
    Route::get('terms-and-conditions', 'terms');
    Route::get('privacy-and-policy', 'privacy');
    Route::get('about-us', 'about');
    Route::get('faq' , 'faq');

});


Route::group(['prefix' => 'wallet' , 'controller' => WalletController::class] , function () {
    Route::get('', 'index');
    Route::post('deposit', 'deposit');
    Route::post('withdraw', 'withdraw');
});


Route::post('contact-us' , ContactUsController::class);


//Order Requirments
Route::get('periodic-examination/{city}' , function($city)
{
    $periodicExaminations = CyPeriodic::with('city')->where('city_id' , $city)->active()->get();
    return apiResponse(200, __('messages.data_returned_successfully' , ['attr' => __('messages.periodicExaminations')]) ,
    CyPeriodicResource::collection($periodicExaminations));
});



Route::prefix('orders')->controller(OrderController::class)->group(function () {
    Route::post('periodic-examination', 'periodicExamination');
    Route::post('payment/{order}' , 'payment');

    //get orders
    Route::get('' , 'index');
});

//get nearby providers
Route::get('nearby-providers' , [ProviderController::class , 'nearbyProviders']);
//route express service
Route::get('express-services' , [ExpressServiceController::class , 'index']);
Route::post('express-services' , [ExpressServiceController::class , 'store']);
Route::get('my-express-services' , [ExpressServiceController::class , 'myExpressServices']);

//get all notifications
Route::get('notifications' , [NotificationController::class , 'index']);
Route::get('notification/{id}' , [NotificationController::class , 'show']);
Route::post('accept-offer/{id}' , [NotificationController::class , 'acceptOffer']);
Route::post('reject-offer/{id}' , [NotificationController::class , 'rejectOffer']);

//get my orders
Route::get('my-orders' , [OrderController::class , 'myOrders']);
Route::get('order/{id}' , [OrderController::class , 'show']);
Route::get('orders' , [OrderController::class , 'ordersByStatus']);
