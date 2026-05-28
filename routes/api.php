<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MediaItemController;

Route::get('/media', [MediaItemController::class, 'index']);
Route::post('/media', [MediaItemController::class, 'store']);
Route::delete('/media/{id}', [MediaItemController::class, 'destroy']);
Route::put('/media/{id}', [MediaItemController::class, 'update']);
