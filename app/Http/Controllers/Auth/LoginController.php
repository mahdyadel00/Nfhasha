<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request)
    {
        if(auth()->attempt($request->only('email', 'password'), $request->remember)){
            if(auth()->user()->role === 'admin'){
                return redirect()->route('manage.home');
            }
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }
}
