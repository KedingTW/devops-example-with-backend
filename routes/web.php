<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Line\LineWebhookController;
use App\Http\Controllers\Wecom\WecomWebhookController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/wecom/webhook', [WecomWebhookController::class, 'check']);
Route::post('/wecom/webhook', [WecomWebhookController::class, 'receive']);
Route::post('/line/webhook', [LineWebhookController::class, 'receive']);
