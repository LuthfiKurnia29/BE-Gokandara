<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KonsumenController;
use App\Http\Controllers\BlokController;
use App\Http\Controllers\CalendarController;
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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FollowupMonitoringController;
use App\Http\Controllers\PropertiController;
use App\Http\Controllers\TargetController;
use App\Http\Controllers\RiwayatPembayaranController;
use App\Http\Controllers\NotifikasiController;
use Illuminate\Support\Facades\Mail;

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
Route::middleware('auth:sanctum')->delete('konsumen-delete-approval/{id}', [KonsumenController::class, 'approveDeleteAdmin']);

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
Route::middleware('auth:sanctum')->delete('chatting', [ChattingController::class, 'destroy']);
Route::middleware('auth:sanctum')->get('chatting-last', [ChattingController::class, 'lastChatting']);

// Properti
Route::middleware('auth:sanctum')->post('properti', [PropertiController::class, 'store']);
Route::middleware('auth:sanctum')->get('properti', [PropertiController::class, 'index']);
Route::middleware('auth:sanctum')->get('properti/{id}', [PropertiController::class, 'show']);
Route::middleware('auth:sanctum')->put('properti/{id}', [PropertiController::class, 'update']);
Route::middleware('auth:sanctum')->delete('properti/{id}', [PropertiController::class, 'destroy']);

// Notifikasi
Route::middleware('auth:sanctum')->get('notifikasi', [NotifikasiController::class, 'index']);
Route::middleware('auth:sanctum')->get('notifikasi-unread', [NotifikasiController::class, 'indexUnread']);
Route::middleware('auth:sanctum')->post('notifikasi-read/{id}', [NotifikasiController::class, 'read']);
Route::middleware('auth:sanctum')->post('notifikasi-read-all', [NotifikasiController::class, 'readAll']);
Route::middleware('auth:sanctum')->get('notifikasi-count', [NotifikasiController::class, 'count']);
Route::middleware('auth:sanctum')->delete('notifikasi/{id}', [NotifikasiController::class, 'destroy']);

// Referensi
Route::middleware('auth:sanctum')->get('referensi', [RefrensiController::class, 'getAllRefrence']);

// Projek-all
Route::middleware('auth:sanctum')->get('all-projek', [ProjekController::class, 'allProject']);

// Blok-all
Route::middleware('auth:sanctum')->get('all-blok', [BlokController::class, 'allBlok']);

// Tipe-all
Route::middleware('auth:sanctum')->get('all-tipe', [TipeController::class, 'allTipe']);

// Unit-all
Route::middleware('auth:sanctum')->get('all-unit', [UnitController::class, 'allUnit']);

// Konsumen
Route::middleware('auth:sanctum')->get('all-konsumen', [KonsumenController::class, 'allKonsumen']);
Route::middleware('auth:sanctum')->get('all-konsumen-by-sales', [KonsumenController::class, 'allKonsumenBySales']);
Route::middleware('auth:sanctum')->get('konsumen-by-supervisor', [KonsumenController::class, 'konsumenBySupervisor']);

// Properti-all
Route::middleware('auth:sanctum')->get('all-properti', [PropertiController::class, 'allProperti']);

// transaksi
Route::middleware('auth:sanctum')->get('list-transaksi', [TransaksiController::class, 'listTransaksi']);
Route::middleware('auth:sanctum')->post('create-transaksi', [TransaksiController::class, 'createTransaksi']);
Route::middleware('auth:sanctum')->get('get-transaksi/{id}', [TransaksiController::class, 'getTransaksi']);
Route::middleware('auth:sanctum')->put('update-transaksi/{id}', [TransaksiController::class, 'updateTransaksi']);
Route::middleware('auth:sanctum')->delete('delete-transaksi/{id}', [TransaksiController::class, 'deleteTransaksi']);
Route::middleware('auth:sanctum')->post('update-status-transaksi/{id}', [TransaksiController::class, 'updateStatusTransaksi']);

// Riwayat Pembayaran
Route::middleware('auth:sanctum')->get('riwayat-pembayaran/{id}', [RiwayatPembayaranController::class, 'index']);
Route::middleware('auth:sanctum')->post('create-riwayat-pembayaran', [RiwayatPembayaranController::class, 'store']);
Route::middleware('auth:sanctum')->put('update-riwayat-pembayaran/{id}', [RiwayatPembayaranController::class, 'update']);
Route::middleware('auth:sanctum')->delete('delete-riwayat-pembayaran/{id}', [RiwayatPembayaranController::class, 'destroy']);

// Follow-Up Monitoring
Route::middleware('auth:sanctum')->get('list-follow-up', [FollowupMonitoringController::class, 'ListFollowUp']);
Route::middleware('auth:sanctum')->post('create-follow-up', [FollowupMonitoringController::class, 'CreateFollowUp']);
Route::middleware('auth:sanctum')->put('update-follow-up/{id}', [FollowupMonitoringController::class, 'UpdateFollowUp']);
Route::middleware('auth:sanctum')->delete('delete-follow-up/{id}', [FollowupMonitoringController::class, 'DeleteFollowUp']);
Route::middleware('auth:sanctum')->post('update-status-follow-up/{id}', [FollowupMonitoringController::class, 'updateStatus']);

// Calendar
Route::middleware('auth:sanctum')->get('get-calendar', [CalendarController::class, 'getCalendar']);
Route::middleware('auth:sanctum')->get('get-calendar/{id}', [CalendarController::class, 'getCalendarById']);
Route::middleware('auth:sanctum')->post('create-calendar', [CalendarController::class, 'createDataCalendar']);
Route::middleware('auth:sanctum')->put('update-calendar/{id}', [CalendarController::class, 'updateDataCalendar']);
Route::middleware('auth:sanctum')->delete('delete-calendar/{id}', [CalendarController::class, 'deleteDataCalendar']);

// Dashboard
Route::middleware('auth:sanctum')->get('followup', [FollowupMonitoringController::class, 'getAllFollowUps']);
Route::middleware('auth:sanctum')->get('followup-today', [DashboardController::class, 'getFollowUpToday']);
Route::middleware('auth:sanctum')->get('followup-tomorrow', [DashboardController::class, 'getFollowUpTomorrow']);
Route::middleware('auth:sanctum')->get('new-konsumens', [DashboardController::class, 'getNewKonsumens']);

// Target
Route::middleware('auth:sanctum')->prefix('target')->group(function () {
    Route::post('', [TargetController::class, 'store']);
    Route::get('', [TargetController::class, 'index']);
    Route::get('{id}', [TargetController::class, 'show']);
    Route::put('{id}', [TargetController::class, 'update']);
    Route::delete('{id}', [TargetController::class, 'destroy']);
    Route::get('{id}/achieved', [TargetController::class, 'getAchievedUser']);
});

Route::get('/send-mail', function () {
    $data = ['title' => 'Hello', 'content' => 'Test email via Brevo SMTP'];
    Mail::to('lkurniahadi@gmail.com')->send(new \App\Mail\NotifMail($data));
    return 'Email sent!';
});
