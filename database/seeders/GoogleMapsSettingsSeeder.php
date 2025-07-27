<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Integrazione;

class GoogleMapsSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Integrazione::set(
            'google_maps_api_key',
            'AIzaSyCq4OBTYMQTc4EriJquqsltsLqFaP9dXBY',
            'string',
            'Chiave API di Google Maps per l\'autocompletamento degli indirizzi'
        );
    }
}