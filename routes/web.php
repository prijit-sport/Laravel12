<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\AiController; // 1. เพิ่มการเรียกใช้งาน AiController

use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index']);


Route::get('/stock/{symbol?}', [StockController::class, 'show']);


Route::get('/stock/{symbol}/chart-data', [StockController::class, 'chartData']);
Route::get('/stock/{symbol}/company-info', [StockController::class, 'companyInfo']);

Route::get('/compare', [CompareController::class, 'index']);

Route::get('/watchlist', [WatchlistController::class, 'index']);
Route::post('/watchlist', [WatchlistController::class, 'store']);
Route::delete('/watchlist/{id}', [WatchlistController::class, 'destroy']);

Route::get('/us-screener', [\App\Http\Controllers\UsScreenerController::class, 'index']);

// 2. เพิ่ม Route สำหรับยิงคำถามไปหา AI (Ollama)
Route::post('/ask-ai', [AiController::class, 'askAi']);