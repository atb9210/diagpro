<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Integrazione;

class OpenAISettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verifica se l'integrazione esiste già
        $existing = Integrazione::where('chiave', 'openai_api_key')->first();
        
        if (!$existing) {
            Integrazione::create([
                'chiave' => 'openai_api_key',
                'tipo' => 'password',
                'valore' => '',
                'descrizione' => 'Chiave API di OpenAI per integrazione con servizi AI'
            ]);
            
            echo "Integrazione OpenAI API Key creata con successo!\n";
        } else {
            echo "Integrazione OpenAI API Key già esistente.\n";
        }
    }
}