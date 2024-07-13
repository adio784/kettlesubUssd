<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\USSDController;

Route::post('/ussd_response', [USSDController::class, 'UssdService']);
