<?php

use App\Http\Controllers\ProcessUSSDController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/ussd_response', [ProcessUSSDController::class, 'handleServices']);
