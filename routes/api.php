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
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\ChattingController;

// Authentication & Authorization
Route::post('login', [LoginController::class, 'authenticate']);
Route::middleware('auth:sanctum')->post('logout', [LoginController::class, 'logout']);
Route::middleware('auth:sanctum')->post('me', [UserController::class, 'me']);
Route::middleware('auth:sanctum')->post('user-role', [UserRoleController::class, 'getRoleByUserId']);
Route::middleware('auth:sanctum')->get('role', [RoleController::class, 'getAllRole']);

// User 
Route::middleware('auth:sanctum')->get('user', [UserController::class, 'index']);
Route::middleware('auth:sanctum')->get('user-spv', [UserController::class, 'getUserSpvRole']);
Route::middleware('auth:sanctum')->get('user/{id}', [UserController::class, 'show']);
Route::middleware('auth:sanctum')->post('user', [UserController::class, 'store']);
Route::middleware('auth:sanctum')->put('user/{id}', [UserController::class, 'update']);
Route::middleware('auth:sanctum')->delete('user/{id}', [UserController::class, 'destroy']);

// Konsumen
Route::middleware('auth:sanctum')->get('konsumen', [KonsumenController::class, 'index']);
Route::middleware('auth:sanctum')->get('konsumen/{id}', [KonsumenController::class, 'show']);
Route::middleware('auth:sanctum')->post('konsumen', [KonsumenController::class, 'store']);
Route::middleware('auth:sanctum')->put('konsumen/{id}', [KonsumenController::class, 'update']);
Route::middleware('auth:sanctum')->delete('konsumen/{id}', [KonsumenController::class, 'destroy']);

// Projek
Route::middleware('auth:sanctum')->get('projek', [ProjekController::class, 'index']);
Route::middleware('auth:sanctum')->get('projek/{id}', [ProjekController::class, 'show']);
Route::middleware('auth:sanctum')->post('projek', [ProjekController::class, 'store']);
Route::middleware('auth:sanctum')->put('projek/{id}', [ProjekController::class, 'update']);
Route::middleware('auth:sanctum')->delete('projek/{id}', [ProjekController::class, 'destroy']);

// Blok
Route::middleware('auth:sanctum')->get('blok', [BlokController::class, 'index']);
Route::middleware('auth:sanctum')->get('blok/{id}', [BlokController::class, 'show']);
Route::middleware('auth:sanctum')->post('blok', [BlokController::class, 'store']);
Route::middleware('auth:sanctum')->put('blok/{id}', [BlokController::class, 'update']);
Route::middleware('auth:sanctum')->delete('blok/{id}', [BlokController::class, 'destroy']);

// Tipe
Route::middleware('auth:sanctum')->get('tipe', [TipeController::class, 'index']);
Route::middleware('auth:sanctum')->get('tipe/{id}', [TipeController::class, 'show']);
Route::middleware('auth:sanctum')->post('tipe', [TipeController::class, 'store']);
Route::middleware('auth:sanctum')->put('tipe/{id}', [TipeController::class, 'update']);
Route::middleware('auth:sanctum')->delete('tipe/{id}', [TipeController::class, 'destroy']);

// Unit
Route::middleware('auth:sanctum')->get('unit', [UnitController::class, 'index']);
Route::middleware('auth:sanctum')->get('unit/{id}', [UnitController::class, 'show']);
Route::middleware('auth:sanctum')->post('unit', [UnitController::class, 'store']);
Route::middleware('auth:sanctum')->put('unit/{id}', [UnitController::class, 'update']);
Route::middleware('auth:sanctum')->delete('unit/{id}', [UnitController::class, 'destroy']);

// Prospek
Route::middleware('auth:sanctum')->get('prospek', [prospekController::class, 'index']);
Route::middleware('auth:sanctum')->get('prospek/{id}', [prospekController::class, 'show']);
Route::middleware('auth:sanctum')->post('prospek', [prospekController::class, 'store']);
Route::middleware('auth:sanctum')->put('prospek/{id}', [prospekController::class, 'update']);
Route::middleware('auth:sanctum')->delete('prospek/{id}', [prospekController::class, 'destroy']);

// Chatting
Route::middleware('auth:sanctum')->get('chatting', [ChattingController::class, 'index']);
Route::middleware('auth:sanctum')->post('chatting', [ChattingController::class, 'store']);
Route::middleware('auth:sanctum')->put('chatting/{id}', [ChattingController::class, 'update']);
Route::middleware('auth:sanctum')->delete('chatting/{id}', [ChattingController::class, 'destroy']);
Route::middleware('auth:sanctum')->get('chatting-last', [ChattingController::class, 'lastChatting']);

// Referensi
Route::middleware('auth:sanctum')->get('referensi', [RefrensiController::class, 'getAllRefrence']);

// Projek-all
Route::middleware('auth:sanctum')->get('all-projek', [ProjekController::class, 'allProject']);

// transaksi
Route::middleware('auth:sanctum')->get('list-transaksi', [TransaksiController::class, 'listTransaksi']);
Route::middleware('auth:sanctum')->post('create-transaksi', [TransaksiController::class, 'createTransaksi']);
Route::middleware('auth:sanctum')->put('update-transaksi/{id}', [TransaksiController::class, 'updateTransaksi']);
Route::middleware('auth:sanctum')->delete('delete-transaksi/{id}', [TransaksiController::class, 'deleteTransaksi']);
Route::middleware('auth:sanctum')->post('update-status-transaksi/{id}', [TransaksiController::class, 'updateStatusTransaksi']);