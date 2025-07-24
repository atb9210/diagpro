<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Impostazione;

class OpenAISettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verifica se l'impostazione esiste già
        $existing = Impostazione::where('chiave', 'openai_api_key')->first();
        
        if (!$existing) {
            Impostazione::create([
                'chiave' => 'openai_api_key',
                'tipo' => 'password',
                'valore' => '',
                'descrizione' => 'Chiave API di OpenAI per integrazione con servizi AI'
            ]);
            
            echo "Impostazione OpenAI API Key creata con successo!\n";
        } else {
            echo "Impostazione OpenAI API Key già esistente.\n";
        }
    }
}