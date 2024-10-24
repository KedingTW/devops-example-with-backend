<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Line\LineWebhookController;

Route::get('/', function () {
    return view('welcome');
});


Route::post('/wecom/webhook', [LineWebhookController::class, 'receive']);
Route::post('/line/webhook', [LineWebhookController::class, 'receive']);
