<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\HomeSlider;
use Illuminate\Http\Request;

class HomeSlidersController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $homeSliders = HomeSlider::latest()->get();

        return apiResponse(200 , __('messages.data_returned_successfully' , ['attr' => __('messages.home_sliders')]) , $homeSliders->pluck('cover_url'));
    }
}
