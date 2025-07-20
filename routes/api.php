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

// Authentication & Authorization
Route::post('login', [LoginController::class, 'authenticate']);
Route::middleware('auth:sanctum')->post('logout', [LoginController::class, 'logout']);
Route::middleware('auth:sanctum')->post('me', [UserController::class, 'me']);
Route::middleware('auth:sanctum')->post('user-role', [UserRoleController::class, 'getRoleByUserId']);
Route::get('role', [RoleController::class, 'getAllRole']);

// User 
Route::get('user', [UserController::class, 'index']);
Route::post('user', [UserController::class, 'store']);
Route::put('user', [UserController::class, 'update']);
Route::delete('user/{id}', [UserController::class, 'destroy']);

// Konsumen
Route::get('konsumen', [KonsumenController::class, 'index']);
Route::post('konsumen', [KonsumenController::class, 'store']);
Route::put('konsumen', [KonsumenController::class, 'update']);
Route::delete('konsumen/{id}', [KonsumenController::class, 'destroy']);

// Projek
Route::get('projek', [ProjekController::class, 'index']);
Route::post('projek', [ProjekController::class, 'store']);
Route::put('projek', [ProjekController::class, 'update']);
Route::delete('projek/{id}', [ProjekController::class, 'destroy']);

// Blok
Route::get('blok', [BlokController::class, 'index']);
Route::post('blok', [BlokController::class, 'store']);
Route::put('blok', [BlokController::class, 'update']);
Route::delete('blok/{id}', [BlokController::class, 'destroy']);

// Tipe
Route::get('tipe', [TipeController::class, 'index']);
Route::post('tipe', [TipeController::class, 'store']);
Route::put('tipe', [TipeController::class, 'update']);
Route::delete('tipe/{id}', [TipeController::class, 'destroy']);

// Unit
Route::get('unit', [UnitController::class, 'index']);
Route::post('unit', [UnitController::class, 'store']);
Route::put('unit', [UnitController::class, 'update']);
Route::delete('unit/{id}', [UnitController::class, 'destroy']);

// Prospek
Route::get('prospek', [prospekController::class, 'index']);
Route::post('prospek', [prospekController::class, 'store']);
Route::put('prospek', [prospekController::class, 'update']);
Route::delete('prospek/{id}', [prospekController::class, 'destroy']);

// Referensi
Route::get('referensi', [RefrensiController::class, 'getAllRefrence']);

// Projek-all
Route::get('all-projek', [ProjekController::class, 'allProject']);