<?php

use YouCan\Http\Controllers\CallbackController;
use YouCan\Http\Controllers\QantraBounceController;
use YouCan\Http\Controllers\TokenRefreshController;
use YouCan\Http\Middleware\YouCanAuthenticate;
use YouCan\Http\Middleware\YouCanCSPHeaders;
use Illuminate\Support\Facades\Route;

Route::get('/youcan/refresh-token', TokenRefreshController::class)->name('youcan.refresh_token');
Route::get('/youcan/qantra-bounce', QantraBounceController::class)->name('youcan.qantra-bounce');

Route::group([
    'middleware' => [YouCanAuthenticate::class, YouCanCSPHeaders::class]
],function () {
    Route::get('/youcan/callback', CallbackController::class)->name('youcan.callback');
});
