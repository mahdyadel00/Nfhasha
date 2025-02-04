<?php

use App\Events\MyEvent;
use App\Events\ServiceRequestEvent;
use App\Events\TestEvent;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ChangeLocaleController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

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

Route::get('/', function () {
    return view('welcome');
});


//Auth Routes Start
Route::view('auth/login' , 'auth.login')->name('login')->middleware('guest');
Route::post('auth/login' , LoginController::class);
//Auth Routes End


//Change Locale Start
Route::get('switch-lang/{lang}', ChangeLocaleController::class)->name('changeLocale');


//Send a notification test for all users
Route::get('send-notification' , function () {
    $users = \App\Models\User::all();
    foreach ($users as $user) {
        $user->notify(new \App\Notifications\GeneralNotification('This is a test notification'));
    }
    return 'Notification sent to all users';
});

Route::view('test-channel' , 'test-channel');
