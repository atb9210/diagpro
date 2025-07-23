@extends('shop.layout')

@section('title', 'Conferma Richiesta - ' . $shop->nome)

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header di conferma -->
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Richiesta Inviata con Successo!</h1>
            <p class="text-lg text-gray-600">La tua richiesta d'ordine è stata ricevuta e sarà validata dal nostro team.</p>
            <p class="text-sm text-gray-500 mt-2">Numero richiesta: <span class="font-semibold">{{ $richiesta->numero_richiesta }}</span></p>
        </div>

        <!-- Stato della richiesta -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Stato della Richiesta</h2>
                    <p class="text-sm text-gray-600">Riceverai aggiornamenti via email</p>
                </div>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($richiesta->stato === 'in_attesa_validazione') bg-yellow-100 text-yellow-800
                        @elseif($richiesta->stato === 'in_validazione') bg-blue-100 text-blue-800
                        @elseif($richiesta->stato === 'approvato') bg-green-100 text-green-800
                        @elseif($richiesta->stato === 'rifiutato') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        @if($richiesta->stato === 'in_attesa_validazione')
                            <svg class="-ml-1 mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                            In Attesa di Validazione
                        @elseif($richiesta->stato === 'in_validazione')
                            <svg class="-ml-1 mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            In Validazione
                        @elseif($richiesta->stato === 'approvato')
                            <svg class="-ml-1 mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Approvato
                        @elseif($richiesta->stato === 'rifiutato')
                            <svg class="-ml-1 mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            Rifiutato
                        @else
                            {{ $richiesta->stato_label }}
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Dettagli della richiesta -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Dettagli Richiesta</h2>
                
                <!-- Dati cliente -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Dati Cliente</h3>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p><span class="font-medium">Nome:</span> {{ $richiesta->dati_cliente['nome'] }}</p>
                        <p><span class="font-medium">Email:</span> {{ $richiesta->dati_cliente['email'] }}</p>
                        <p><span class="font-medium">Telefono:</span> {{ $richiesta->dati_cliente['telefono'] }}</p>
                    </div>
                </div>

                <!-- Dati spedizione -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Indirizzo di Spedizione</h3>
                    <div class="text-sm text-gray-600">
                        <p>{{ $richiesta->dati_spedizione['indirizzo'] }}</p>
                        <p>{{ $richiesta->dati_spedizione['cap'] }} {{ $richiesta->dati_spedizione['citta'] }} ({{ $richiesta->dati_spedizione['provincia'] }})</p>
                        <p>{{ $richiesta->dati_spedizione['nazione'] ?? 'Italia' }}</p>
                    </div>
                </div>

                @if($richiesta->note)
                <!-- Note -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Note</h3>
                    <p class="text-sm text-gray-600">{{ $richiesta->note }}</p>
                </div>
                @endif
            </div>

            <!-- Prodotti ordinati -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Prodotti Richiesti</h2>
                
                <div class="space-y-4">
                    @foreach($richiesta->prodotti as $prodotto)
                        <div class="flex justify-between items-start border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ $prodotto->pivot->nome_prodotto }}</h4>
                                @if($prodotto->pivot->sku)
                                    <p class="text-xs text-gray-500">SKU: {{ $prodotto->pivot->sku }}</p>
                                @endif
                                <p class="text-sm text-gray-600">Quantità: {{ $prodotto->pivot->quantita }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-gray-900">€ {{ number_format($prodotto->pivot->prezzo_personalizzato, 2, ',', '.') }}</p>
                                <p class="text-sm text-gray-500">Subtotale: € {{ number_format($prodotto->pivot->subtotale, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Totale -->
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Totale</span>
                        <span class="text-lg font-bold shop-primary-text">€ {{ number_format($richiesta->totale, 2, ',', '.') }}</span>
                    </div>
                    @if($richiesta->totale_spedizione > 0)
                        <div class="flex justify-between items-center text-sm text-gray-600 mt-1">
                            <span>Spedizione</span>
                            <span>€ {{ number_format($richiesta->totale_spedizione, 2, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informazioni aggiuntive -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
            <div class="flex items-start">
                <svg class="flex-shrink-0 h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Cosa succede ora?</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Il nostro team verificherà la disponibilità dei prodotti</li>
                            <li>Riceverai una email di conferma entro 24 ore</li>
                            <li>Una volta approvata, la richiesta diventerà un ordine ufficiale</li>
                            <li>Ti invieremo i dettagli per il pagamento e la spedizione</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Azioni -->
        <div class="text-center mt-8">
            <a href="{{ route('shop.index', $shop->slug) }}" 
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md btn-shop-primary hover:opacity-90 transition-opacity">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Torna al Shop
            </a>
        </div>
    </div>
</div>
@endsection