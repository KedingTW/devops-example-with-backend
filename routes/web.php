<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/test', function() {
    return [ 'test'=> 'ok' ];
});

Route::get('/user/{id}', [UserController::class, 'show']);