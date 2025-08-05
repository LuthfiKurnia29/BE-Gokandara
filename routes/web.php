<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path("app/public/{$path}");

    if (!file_exists($fullPath)) {
        abort(404);
    }

    $file = File::get($fullPath);
    $mimeType = File::mimeType($fullPath);

    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Access-Control-Allow-Origin', '*');
})->where('path', '.*');

Route::get('/', function () {
    return view('welcome');
});
