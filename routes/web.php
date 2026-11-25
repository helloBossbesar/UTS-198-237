<?php

use App\Http\Controllers\BiodataController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

// API Routes
Route::prefix('api/biodata')->group(function () {
    Route::get('/', [BiodataController::class, 'index']);
    Route::get('/{id}', [BiodataController::class, 'show']);
    Route::post('/', [BiodataController::class, 'store']);
    Route::post('/{id}', [BiodataController::class, 'update']);
    Route::put('/{id}', [BiodataController::class, 'update']);
    Route::delete('/{id}', [BiodataController::class, 'destroy']);
});
