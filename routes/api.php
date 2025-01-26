<?php

use App\Http\Controllers\Api\DataReceiverController;
use Illuminate\Support\Facades\Route;

Route::post('/data', [DataReceiverController::class, 'store']);
