<?php

use Illuminate\Support\Facades\Route;

Route::get('/account-locked', 'MessageController@getAccountLocked')->name('account-locked');
