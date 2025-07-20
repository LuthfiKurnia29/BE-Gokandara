<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KonsumenController;
use App\Http\Controllers\BlokController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProjekController;
use App\Http\Controllers\ProspekController;
use App\Http\Controllers\RefrensiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TipeController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;

Route::post('login', [LoginController::class, 'authenticate']);
Route::middleware('auth:sanctum')->post('logout', [LoginController::class, 'logout']);
Route::resource('user', UserController::class);
Route::resource('konsumen', KonsumenController::class);
Route::resource('projek', ProjekController::class);
Route::resource('blok', BlokController::class);
Route::resource('tipe', TipeController::class);
Route::resource('unit', UnitController::class);
Route::resource('prospek', ProspekController::class);
Route::middleware('auth:sanctum')->post('user-role', [UserRoleController::class, 'getRoleByUserId']);
Route::get('role', [RoleController::class, 'getAllRole']);
Route::middleware('auth:sanctum')->post('me', [UserController::class, 'me']);
Route::get('referensi', [RefrensiController::class, 'getAllRefrence']);
Route::get('all-projek', [ProjekController::class, 'allProject']);