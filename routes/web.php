<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrdiniController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Route::resource('ordini', OrdiniController::class); // Commentato per usare Filament
});
