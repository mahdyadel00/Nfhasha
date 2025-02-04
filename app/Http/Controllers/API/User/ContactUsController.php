<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\ContactUsRequest;
use App\Models\ContactUs;
use App\Models\ContactUsProviders;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ContactUsRequest $request)
    {
        $model = $request->routeIs('provider.contact-us') ? ContactUsProviders::class : ContactUs::class;

        $model::create($request->all() + ['phone' => auth()->user()->phone , 'name' => auth()->user()->name]);


        return apiResponse(200, __('manage.created_successfully'));
    }
}
