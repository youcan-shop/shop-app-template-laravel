<?php

use Illuminate\Support\Facades\Route;
use YouCan\Http\Middleware\YouCanAuthenticate;
use YouCan\Http\Middleware\YouCanCSPHeaders;
use YouCan\Services\CurrentAuthSession;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return CurrentAuthSession::getCurrentSession();
})->middleware([YouCanAuthenticate::class, YouCanCSPHeaders::class]);
