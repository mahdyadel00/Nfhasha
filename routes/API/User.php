<?php

use App\Http\Controllers\API\User\{AppController,
    ContactUsController,
    ExpressServiceController,
    MainServicesController,
    NotificationController,
    UserController,
    VehiclesController,
    VehiclesInfoController,
    WalletController,
    ProviderController,
    PaymentController
};
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\API\User\OrderController;
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
Route::get('profile', [UserController::class, 'profile']);
//fcm_token
Route::post('fcm-token' , [UserController::class , 'fcmToken']);
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
Route::get('periodic-examination/{city}'  , [OrderController::class , 'cyPeriodics']);

Route::prefix('orders')->controller(OrderController::class)->group(function () {
    Route::post('periodic-examination', 'createOrder');
    Route::post('payment/{order}' , 'payment');
    Route::get('' , 'index');
});

//get nearby providers
Route::get('nearby-providers' , [ProviderController::class , 'nearbyProviders']);
//route express service
Route::get('express-services' , [ExpressServiceController::class , 'index']);
Route::post('express-services' , [ExpressServiceController::class , 'store']);
Route::get('my-express-services' , [ExpressServiceController::class , 'myExpressServices']);
Route::get('express-service/{id}' , [ExpressServiceController::class , 'show']);



//get all notifications
Route::get('notifications' , [NotificationController::class , 'index']);
Route::get('notification/{id}' , [NotificationController::class , 'show']);
Route::post('accept-offer/{id}' , [NotificationController::class , 'acceptOffer']);
Route::post('reject-offer/{id}' , [NotificationController::class , 'rejectOffer']);

//get my orders
Route::get('my-orders' , [OrderController::class , 'myOrders']);
Route::get('order/{id}' , [OrderController::class , 'show']);
Route::get('orders' , [OrderController::class , 'ordersByStatus']);
Route::post('cancel-order/{id}' , [OrderController::class , 'cancelOrder']);
Route::post('reject-order/{id}' , [OrderController::class , 'rejectOrder']);
Route::post('rate/{order_id}' , [OrderController::class , 'rate']);
//start chat\
Route::post('start-chat/{id}' , [ChatController::class , 'startChat']);
//send message
Route::post('send-message/{id}' , [MessageController::class , 'sendMessage']);
//get messages
Route::get('messages/{id}' , [MessageController::class , 'messages']);
//get chat
Route::get('chats/{id}' , [ChatController::class , 'chats']);
//get chat
Route::get('chat/{order_id}/{id}' , [ChatController::class , 'chat']);

//payment
Route::post('/initiate-payment', [PaymentController::class, 'initiatePayment']);

//cy_periodics
Route::post('cy_periodics' , [MainServicesController::class , 'store']);
//use coupon
// Route::post('coupon' , [ServiceOfferController::class , 'coupon']);

//get service maintenance
Route::get('service-maintenance' , [MainServicesController::class , 'index']);
Route::get('service-maintenance/{id}' , [MainServicesController::class , 'show']);
use Kreait\Firebase\Factory;

Route::get('/check-firebase', function () {
    $path = base_path(config('firebase.credentials'));

    if (!file_exists($path)) {
        return response()->json(['error' => 'Firebase service account file not found!', 'path' => $path]);
    }

    return response()->json(['success' => 'Firebase service account file found!', 'path' => $path]);
});


Route::get('/firebase-test', function () {
    $serviceAccountPath = base_path('storage/app/firebase-admin.json'); // المسار المباشر للملف

    if (!file_exists($serviceAccountPath)) {
        return response()->json(['error' => 'Firebase service account file not found!', 'path' => $serviceAccountPath], 500);
    }

    $factory = (new Factory)->withServiceAccount($serviceAccountPath);
    $database = $factory->createDatabase();

    return response()->json(['message' => 'Firebase connected successfully!']);
});