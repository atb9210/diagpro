<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class OpenAIExampleController extends Controller
{
    public function __construct(
        private OpenAIService $openAIService
    ) {
        // Middleware di autenticazione se necessario
        // $this->middleware('auth:api');
    }

    /**
     * Genera testo usando OpenAI
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|max:2000',
            'max_tokens' => 'nullable|integer|min:1|max:2000',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'model' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dati di input non validi',
                'errors' => $validator->errors()
            ], 422);
        }

        $options = [];
        if ($request->has('max_tokens')) {
            $options['max_tokens'] = $request->max_tokens;
        }
        if ($request->has('temperature')) {
            $options['temperature'] = $request->temperature;
        }
        if ($request->has('model')) {
            $options['model'] = $request->model;
        }

        $result = $this->openAIService->generateText($request->prompt, $options);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Chat con sistema di prompt
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function chat(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'messages' => 'required|array|min:1',
            'messages.*.role' => 'required|string|in:system,user,assistant',
            'messages.*.content' => 'required|string|max:2000',
            'max_tokens' => 'nullable|integer|min:1|max:2000',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'model' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dati di input non validi',
                'errors' => $validator->errors()
            ], 422);
        }

        $options = [];
        if ($request->has('max_tokens')) {
            $options['max_tokens'] = $request->max_tokens;
        }
        if ($request->has('temperature')) {
            $options['temperature'] = $request->temperature;
        }
        if ($request->has('model')) {
            $options['model'] = $request->model;
        }

        $result = $this->openAIService->chat($request->messages, $options);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Ottieni modelli disponibili
     * 
     * @return JsonResponse
     */
    public function models(): JsonResponse
    {
        $result = $this->openAIService->getAvailableModels();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Testa la connessione
     * 
     * @return JsonResponse
     */
    public function test(): JsonResponse
    {
        $result = $this->openAIService->testConnection();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Genera descrizione prodotto
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function generateProductDescription(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:200',
            'features' => 'nullable|array',
            'features.*' => 'string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dati di input non validi',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->openAIService->generateProductDescription(
            $request->product_name,
            $request->features ?? []
        );

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Analizza il sentiment di un testo
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function analyzeSentiment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dati di input non validi',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->openAIService->analyzeSentiment($request->text);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Endpoint per assistente clienti
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function customerAssistant(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:500',
            'context' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dati di input non validi',
                'errors' => $validator->errors()
            ], 422);
        }

        $systemPrompt = "Sei un assistente clienti professionale per Diagpro, un sistema di gestione aziendale. Rispondi in modo cortese, professionale e utile. Se non conosci la risposta, suggerisci di contattare il supporto tecnico.";
        
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        if ($request->has('context') && !empty($request->context)) {
            $messages[] = ['role' => 'system', 'content' => 'Contesto aggiuntivo: ' . $request->context];
        }

        $messages[] = ['role' => 'user', 'content' => $request->question];

        $result = $this->openAIService->chat($messages, [
            'max_tokens' => 300,
            'temperature' => 0.7
        ]);

        return response()->json($result, $result['success'] ? 200 : 500);
    }
}