<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrdiniController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\OpenAIExampleController;

Route::get('/', function () {
    return view('welcome');
});

// Rotte Mini Shop (pubbliche, senza autenticazione)
Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/{slug}', [ShopController::class, 'index'])->name('index');
    Route::get('/{slug}/prodotto/{prodotto}', [ShopController::class, 'prodotto'])->name('prodotto');
    Route::post('/{slug}/carrello/aggiungi', [ShopController::class, 'aggiungiAlCarrello'])->name('carrello.aggiungi');
    Route::get('/{slug}/carrello', [ShopController::class, 'carrello'])->name('carrello');
    Route::get('/{slug}/carrello/conteggio', [ShopController::class, 'conteggioCarrello'])->name('carrello.conteggio');
    Route::post('/{slug}/carrello/aggiorna', [ShopController::class, 'aggiornaCarrello'])->name('carrello.aggiorna');
    Route::get('/{slug}/checkout', [ShopController::class, 'checkout'])->name('checkout');
    Route::post('/{slug}/checkout', [ShopController::class, 'processaOrdine'])->name('checkout.processa');
    Route::get('/{slug}/conferma/{ordine}', [ShopController::class, 'confermaOrdine'])->name('conferma');
});

Route::middleware(['auth'])->group(function () {
    // Route::resource('ordini', OrdiniController::class); // Commentato per usare Filament
});

// Rotte di esempio per OpenAI (protette da autenticazione)
Route::middleware(['auth'])->prefix('api/openai')->name('openai.')->group(function () {
    Route::post('/generate', [OpenAIExampleController::class, 'generateText'])->name('generate');
    Route::post('/chat', [OpenAIExampleController::class, 'chat'])->name('chat');
    Route::get('/models', [OpenAIExampleController::class, 'getModels'])->name('models');
    Route::get('/test', [OpenAIExampleController::class, 'testConnection'])->name('test');
});
