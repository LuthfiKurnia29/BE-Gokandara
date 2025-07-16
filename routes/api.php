<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KonsumenController;
use App\Http\Controllers\BlokController;
use App\Http\Controllers\TipeController;
use App\Http\Controllers\UnitController;

Route::resource('konsumen', KonsumenController::class);
Route::resource('blok', BlokController::class);
Route::resource('tipe', TipeController::class);
Route::resource('unit', UnitController::class);