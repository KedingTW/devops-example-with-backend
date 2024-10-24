<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Line\LineWebhookController;
use App\Http\Controllers\Wecom\WecomWebhookController;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'wecom'], function () {
    Route::get('webhook', [WecomWebhookController::class, 'verify']);
    Route::post('webhook', [WecomWebhookController::class, 'receive']);
});
Route::post('/line/webhook', [LineWebhookController::class, 'receive']);
