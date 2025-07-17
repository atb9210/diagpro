<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class LivewireOverrideServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Questo approccio non funziona perché la classe viene istanziata direttamente
        // nel codice di Livewire, non tramite il container di Laravel
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Sovrascriviamo la funzione tmpfile nel namespace di Livewire
        if (!function_exists('Livewire\Features\SupportFileUploads\tmpfile')) {
            require_once __DIR__ . '/../../app/Overrides/Livewire/tmpfile_override.php';
        }
    }
}