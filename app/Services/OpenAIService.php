<?php

namespace App\Services;

use App\Models\Integrazione;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class OpenAIService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openai.com/v1';
    private int $timeout = 30;
    private int $maxRetries = 3;

    public function __construct()
    {
        $this->apiKey = $this->getApiKey();
    }

    /**
     * Ottieni la chiave API dalle impostazioni
     */
    private function getApiKey(): string
    {
        $setting = Integrazione::where('chiave', 'openai_api_key')->first();
        return $setting ? $setting->valore : '';
    }

    /**
     * Testa la connessione con OpenAI
     */
    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'API Key OpenAI non configurata',
                'error' => 'missing_api_key'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->get($this->baseUrl . '/models');

            if ($response->successful()) {
                $models = $response->json();
                $modelCount = count($models['data'] ?? []);
                
                return [
                    'success' => true,
                    'message' => "Connessione riuscita! Trovati {$modelCount} modelli disponibili.",
                    'models_count' => $modelCount
                ];
            } else {
                $error = $response->json();
                Log::error('OpenAI API Error', ['response' => $error]);
                
                return [
                    'success' => false,
                    'message' => 'Errore di connessione: ' . ($error['error']['message'] ?? 'Errore sconosciuto'),
                    'error' => $error['error']['code'] ?? 'unknown_error'
                ];
            }
        } catch (Exception $e) {
            Log::error('OpenAI Connection Error', ['exception' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Errore di connessione: ' . $e->getMessage(),
                'error' => 'connection_failed'
            ];
        }
    }

    /**
     * Genera testo usando OpenAI
     */
    public function generateText(string $prompt, array $options = []): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'API Key OpenAI non configurata'
            ];
        }

        $defaultOptions = [
            'model' => 'gpt-3.5-turbo',
            'max_tokens' => 150,
            'temperature' => 0.7,
        ];

        $options = array_merge($defaultOptions, $options);

        try {
            $response = $this->makeRequest('/chat/completions', [
                'model' => $options['model'],
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $options['max_tokens'],
                'temperature' => $options['temperature'],
            ]);

            if ($response['success']) {
                return [
                    'success' => true,
                    'text' => $response['data']['choices'][0]['message']['content'] ?? '',
                    'usage' => $response['data']['usage'] ?? null
                ];
            }

            return $response;
        } catch (Exception $e) {
            Log::error('OpenAI Generate Text Error', ['exception' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Errore nella generazione del testo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Chat con sistema di prompt
     */
    public function chat(array $messages, array $options = []): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'API Key OpenAI non configurata'
            ];
        }

        $defaultOptions = [
            'model' => 'gpt-3.5-turbo',
            'max_tokens' => 500,
            'temperature' => 0.7,
        ];

        $options = array_merge($defaultOptions, $options);

        try {
            $response = $this->makeRequest('/chat/completions', [
                'model' => $options['model'],
                'messages' => $messages,
                'max_tokens' => $options['max_tokens'],
                'temperature' => $options['temperature'],
            ]);

            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => $response['data']['choices'][0]['message']['content'] ?? '',
                    'usage' => $response['data']['usage'] ?? null
                ];
            }

            return $response;
        } catch (Exception $e) {
            Log::error('OpenAI Chat Error', ['exception' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Errore nella chat: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Ottieni i modelli disponibili
     */
    public function getAvailableModels(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'API Key OpenAI non configurata'
            ];
        }

        // Cache per 1 ora
        return Cache::remember('openai_models', 3600, function () {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->timeout($this->timeout)
                ->get($this->baseUrl . '/models');

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'success' => true,
                        'models' => $data['data'] ?? []
                    ];
                } else {
                    $error = $response->json();
                    Log::error('OpenAI Models Error', ['response' => $error]);
                    
                    return [
                        'success' => false,
                        'message' => 'Errore nel recupero modelli: ' . ($error['error']['message'] ?? 'Errore sconosciuto')
                    ];
                }
            } catch (Exception $e) {
                Log::error('OpenAI Models Exception', ['exception' => $e->getMessage()]);
                
                return [
                    'success' => false,
                    'message' => 'Errore nel recupero modelli: ' . $e->getMessage()
                ];
            }
        });
    }

    /**
     * Effettua una richiesta HTTP con retry automatico
     */
    private function makeRequest(string $endpoint, array $data): array
    {
        $attempt = 0;
        
        while ($attempt < $this->maxRetries) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->timeout($this->timeout)
                ->post($this->baseUrl . $endpoint, $data);

                if ($response->successful()) {
                    return [
                        'success' => true,
                        'data' => $response->json()
                    ];
                } else {
                    $error = $response->json();
                    
                    // Se è un errore di rate limit, aspetta e riprova
                    if ($response->status() === 429 && $attempt < $this->maxRetries - 1) {
                        sleep(pow(2, $attempt)); // Exponential backoff
                        $attempt++;
                        continue;
                    }
                    
                    Log::error('OpenAI API Error', ['response' => $error, 'attempt' => $attempt + 1]);
                    
                    return [
                        'success' => false,
                        'message' => 'Errore API: ' . ($error['error']['message'] ?? 'Errore sconosciuto'),
                        'error' => $error['error']['code'] ?? 'unknown_error'
                    ];
                }
            } catch (Exception $e) {
                if ($attempt < $this->maxRetries - 1) {
                    sleep(pow(2, $attempt)); // Exponential backoff
                    $attempt++;
                    continue;
                }
                
                Log::error('OpenAI Request Exception', ['exception' => $e->getMessage(), 'attempt' => $attempt + 1]);
                
                return [
                    'success' => false,
                    'message' => 'Errore di connessione: ' . $e->getMessage()
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Numero massimo di tentativi raggiunto'
        ];
    }

    /**
     * Genera descrizione prodotto
     */
    public function generateProductDescription(string $productName, array $features = []): array
    {
        $featuresText = empty($features) ? '' : "\nCaratteristiche: " . implode(', ', $features);
        
        $prompt = "Genera una descrizione accattivante e professionale per il prodotto '{$productName}'.{$featuresText}\n\nLa descrizione deve essere:\n- Coinvolgente e persuasiva\n- Lunga circa 100-150 parole\n- Focalizzata sui benefici per il cliente\n- Professionale ma accessibile";
        
        return $this->generateText($prompt, [
            'max_tokens' => 200,
            'temperature' => 0.8
        ]);
    }

    /**
     * Analizza il sentiment di un testo
     */
    public function analyzeSentiment(string $text): array
    {
        $prompt = "Analizza il sentiment del seguente testo e rispondi solo con: POSITIVO, NEGATIVO o NEUTRO\n\nTesto: {$text}";
        
        $result = $this->generateText($prompt, [
            'max_tokens' => 10,
            'temperature' => 0.1
        ]);
        
        if ($result['success']) {
            $sentiment = trim(strtoupper($result['text']));
            return [
                'success' => true,
                'sentiment' => $sentiment,
                'usage' => $result['usage'] ?? null
            ];
        }
        
        return $result;
    }
}