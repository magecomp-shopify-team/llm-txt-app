<?php

use App\Http\Controllers\LlmController;
use App\Http\Controllers\PlanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/llm/counts', [LlmController::class, 'counts']);
    Route::get('/llm-settings', [LlmController::class, 'show']);
    Route::post('/llm-settings', [LlmController::class, 'update']);
    Route::get('/llm/generate', [LlmController::class, 'generate']);

    Route::get('/plans/{plan}', [PlanController::class, 'index'])->name('billing.cus');

});
