<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OpenAIService;
use App\Models\Impostazione;

class TestOpenAIConnection extends Command
{
    protected $signature = 'openai:test';
    protected $description = 'Test OpenAI API connection';

    public function handle()
    {
        $this->info('Testing OpenAI connection...');
        
        // Verifica impostazione nel database
        $setting = Impostazione::where('chiave', 'openai_api_key')->first();
        
        if (!$setting) {
            $this->error('❌ Impostazione openai_api_key non trovata nel database');
            return 1;
        }
        
        $this->info('✅ Impostazione trovata:');
        $this->line('  - Chiave: ' . $setting->chiave);
        $this->line('  - Tipo: ' . $setting->tipo);
        $this->line('  - Valore: ' . (empty($setting->valore) ? 'VUOTO' : str_repeat('*', min(strlen($setting->valore), 20))));
        $this->line('  - Descrizione: ' . ($setting->descrizione ?? 'N/A'));
        
        if (empty($setting->valore)) {
            $this->error('❌ API Key è vuota');
            return 1;
        }
        
        // Test del servizio
        $this->info('\nTesting OpenAI service...');
        
        try {
            $openAIService = app(OpenAIService::class);
            $result = $openAIService->testConnection();
            
            if ($result['success']) {
                $this->info('✅ ' . $result['message']);
                return 0;
            } else {
                $this->error('❌ ' . $result['message']);
                if (isset($result['error'])) {
                    $this->line('Error code: ' . $result['error']);
                }
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Exception: ' . $e->getMessage());
            $this->line('File: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }
    }
}