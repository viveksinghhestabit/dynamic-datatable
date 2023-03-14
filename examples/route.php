<?php

use Illuminate\Support\Facades\Route;

Route::get('/getUser', 'UserController@getUser')->name('getUser');
