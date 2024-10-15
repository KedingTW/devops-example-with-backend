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

Route::get('/new1', function() {
    return ['new1' => 'ok'];
});

Route::get('/new2', function() {
    return ['new2' => 'ok'];
});