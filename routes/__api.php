<?php

use App\Http\Controllers\ProcessUSSDController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\USSDController;

Route::post('/ussd_response', [ProcessUSSDController::class, 'handleServices']);
