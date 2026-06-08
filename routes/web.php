<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\WatchlistController;

Route::get('/', function () {
    return redirect('/stock/NVDA');
});
Route::get('/stock/{symbol?}', [StockController::class, 'show']);

Route::get('/stock/{symbol}/chart-data', [StockController::class, 'chartData']);

Route::get('/compare', [CompareController::class, 'index']);

Route::get('/watchlist', [WatchlistController::class, 'index']);
Route::post('/watchlist', [WatchlistController::class, 'store']);
Route::delete('/watchlist/{id}', [WatchlistController::class, 'destroy']);

Route::get('/us-screener', [\App\Http\Controllers\UsScreenerController::class, 'index']);

