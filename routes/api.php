<?php

use App\Http\Controllers\OpenAIExampleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// OpenAI API Routes
Route::prefix('openai')->group(function () {
    // Test connessione
    Route::get('/test', [OpenAIExampleController::class, 'test']);
    
    // Generazione testo
    Route::post('/generate', [OpenAIExampleController::class, 'generate']);
    
    // Chat
    Route::post('/chat', [OpenAIExampleController::class, 'chat']);
    
    // Modelli disponibili
    Route::get('/models', [OpenAIExampleController::class, 'models']);
    
    // Funzionalità specifiche
    Route::post('/product-description', [OpenAIExampleController::class, 'generateProductDescription']);
    Route::post('/sentiment', [OpenAIExampleController::class, 'analyzeSentiment']);
    Route::post('/customer-assistant', [OpenAIExampleController::class, 'customerAssistant']);
});