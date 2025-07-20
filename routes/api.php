<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KonsumenController;
use App\Http\Controllers\BlokController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TipeController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;

Route::post('login', [LoginController::class, 'authenticate']);
Route::resource('user', UserController::class);
Route::resource('konsumen', KonsumenController::class);
Route::resource('blok', BlokController::class);
Route::resource('tipe', TipeController::class);
Route::resource('unit', UnitController::class);
Route::post('user-role', [UserRoleController::class, 'getRoleByUserId']);
Route::get('role', [RoleController::class, 'getAllRole']);