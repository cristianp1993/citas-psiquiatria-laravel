<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarController;

Route::get('/public/availability', [CalendarController::class, 'availability']);
