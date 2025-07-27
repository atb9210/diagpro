<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Integrazione;

class ObiettivoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Integrazione::set(
            'obiettivo_profitto_mensile',
            '2000',
            'number',
            'Obiettivo di profitto mensile in euro'
        );
    }
}