<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KonsumenController;

Route::resource('konsumen', KonsumenController::class);