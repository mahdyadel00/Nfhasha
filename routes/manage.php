<?php

use App\Http\Controllers\Manage\CitiesController;
use App\Http\Controllers\Manage\DistrictsController;
use App\Http\Controllers\Manage\SplashScreensController;
use App\Http\Controllers\Manage\HomeController;
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

Route::get('/', HomeController::class)->name('home');
Route::resource('splash-screens', SplashScreensController::class);
Route::resource('cities' , CitiesController::class);
Route::resource('districts' , DistrictsController::class);
