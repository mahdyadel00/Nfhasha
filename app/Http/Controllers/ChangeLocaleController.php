<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChangeLocaleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($lang)
    {

        if (array_key_exists($lang, config('app.locales'))) {
            session()->put('locale', $lang);
            logger('Language set to: ' . $lang);
        }
        
        return redirect()->back();
    }
}
