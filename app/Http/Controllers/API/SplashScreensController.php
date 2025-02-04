<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\SplashScreensResource;
use App\Models\SplashScreen;
use Illuminate\Http\Request;

class SplashScreensController extends Controller
{
    public function index()
    {
        $splashScreens = SplashScreen::active()->ordered()->with('translations')->get();

        return apiResponse(200,
        translate('messages.splash_screens_successfully_retrieved') ,
         SplashScreensResource::collection($splashScreens));
    }
}
