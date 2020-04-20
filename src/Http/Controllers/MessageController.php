<?php

namespace Sicaboy\LaravelSecurity\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class MessageController extends Controller
{
    public function getAccountLocked(Request $request) {
        Auth::logout();
        return view('laravel-security::account-locked', []);
    }
}
