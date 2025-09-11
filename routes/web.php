<?php

use App\Http\Controllers\FrontController;
use App\Http\Controllers\ProxyController;
use Illuminate\Support\Facades\Route;


Route::fallback([FrontController::class, 'index']);

Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/', [FrontController::class, 'index'])->name('home');
});

Route::get('/proxy/llms', [ProxyController::class, 'getLlmFileContent']);
