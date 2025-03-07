<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\FAQsReource;
use App\Models\FAQ;
use App\Models\SocialLinks;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public $locale;

    public function __construct()
    {
        $this->locale = request()->header('Accept-Language', config('app.locale'));
    }

    public function terms()
    {
        $terms = settings()->get('terms_and_conditions_' . $this->locale);


        return apiResponse(200,
        __('messages.data_returned_successfully' , ['attr' => __('messages.terms')]),
        [
                'terms' => $terms
        ]);
    }

    public function privacy()
    {
        $privacy = settings()->get('privacy_policy_' . $this->locale);

        return apiResponse(200,
        __('messages.data_returned_successfully' , ['attr' => __('messages.privacy')]),
        [
                'privacy' => $privacy
        ]);
    }

    public function about()
    {
        $about = settings()->get('about_us_' . $this->locale);

        return apiResponse(200,
        __('messages.data_returned_successfully' , ['attr' => __('messages.about')]),
        [
                'about' => $about
        ]);
    }

    public function faq(Request $request)
    {
        $toProvider = $request->routeIs('provider.faq') ? true : false;

        $FAQs = FAQ::where('to_providers', $toProvider)->get();

        return apiResponse(
            200,
            __('messages.data_returned_successfully', ['attr' => __('messages.FAQs')]),
            FAQsReource::collection($FAQs)
        );
    }

    public function links()
    {
        $socialLinks = SocialLinks::get();

        return count($socialLinks) > 0 ?
            apiResponse(200, __('messages.data_returned_successfully', ['attr' => __('messages.social_links')]), $socialLinks) :
            apiResponse(404, __('messages.no_social_links_found'));
    }
}
