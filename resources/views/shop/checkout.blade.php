@extends('shop.layout')

@section('title', 'Checkout - ' . $shop->nome)

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
            <p class="mt-2 text-gray-600">Completa il tuo ordine inserendo i dati di spedizione</p>
        </div>

        @if(empty($carrello))
            <!-- Carrello vuoto -->
            <div class="text-center py-12">
                <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 21a2 2 0 100-4 2 2 0 000 4zM9 21a2 2 0 100-4 2 2 0 000 4z"></path>
                </svg>
                <h2 class="text-2xl font-semibold text-gray-900 mb-2">Il tuo carrello è vuoto</h2>
                <p class="text-gray-600 mb-6">Aggiungi alcuni prodotti al carrello prima di procedere al checkout</p>
                <a href="{{ route('shop.index', $shop->slug) }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white shop-bg-primary hover:opacity-90 transition-opacity">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                    </svg>
                    Continua lo shopping
                </a>
            </div>
        @else
            <form action="{{ route('shop.checkout.processa', $shop->slug) }}" method="POST" id="checkout-form">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Form dati cliente -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Dati di spedizione</h2>
                        
                        <div class="space-y-4">
                            <!-- Nome e Cognome -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                                    <input type="text" id="nome" name="nome" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="{{ old('nome') }}">
                                    @error('nome')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="cognome" class="block text-sm font-medium text-gray-700 mb-1">Cognome *</label>
                                    <input type="text" id="cognome" name="cognome" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="{{ old('cognome') }}">
                                    @error('cognome')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Email e Telefono -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                    <input type="email" id="email" name="email" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="{{ old('email') }}">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Telefono *</label>
                                    <input type="tel" id="telefono" name="telefono" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="{{ old('telefono') }}">
                                    @error('telefono')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Indirizzo -->
                            <div>
                                <label for="indirizzo" class="block text-sm font-medium text-gray-700 mb-1">Indirizzo *</label>
                                <input type="text" id="indirizzo" name="indirizzo" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       value="{{ old('indirizzo') }}">
                                @error('indirizzo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Città, CAP, Provincia -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label for="citta" class="block text-sm font-medium text-gray-700 mb-1">Città *</label>
                                    <input type="text" id="citta" name="citta" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="{{ old('citta') }}">
                                    @error('citta')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="cap" class="block text-sm font-medium text-gray-700 mb-1">CAP *</label>
                                    <input type="text" id="cap" name="cap" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="{{ old('cap') }}">
                                    @error('cap')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="provincia" class="block text-sm font-medium text-gray-700 mb-1">Provincia *</label>
                                    <input type="text" id="provincia" name="provincia" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           value="{{ old('provincia') }}">
                                    @error('provincia')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Note aggiuntive -->
                            <div>
                                <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Note aggiuntive</label>
                                <textarea id="note" name="note" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                          placeholder="Eventuali note per la spedizione...">{{ old('note') }}</textarea>
                                @error('note')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Riepilogo ordine -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Riepilogo ordine</h2>
                        
                        <!-- Prodotti nel carrello -->
                        <div class="space-y-4 mb-6">
                            @foreach($carrello as $prodotto_id => $item)
                                @php
                                    $prodotto = App\Models\Prodotto::find($prodotto_id);
                                @endphp
                                @if($prodotto)
                                    <div class="flex items-center space-x-4 py-3 border-b border-gray-200">
                                        @if($prodotto->immagine)
                                            <img src="{{ Storage::url($prodotto->immagine) }}" 
                                                 alt="{{ $prodotto->nome }}" 
                                                 class="w-16 h-16 object-cover rounded">
                                        @else
                                            <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        @endif
                                        
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900">{{ $prodotto->nome }}</h4>
                                            <p class="text-sm text-gray-500">{{ $prodotto->descrizione_breve }}</p>
                                            <div class="flex justify-between items-center mt-2">
                                                <span class="text-sm text-gray-600">Quantità: {{ $item['quantita'] }}</span>
                                                <span class="font-semibold shop-primary-text">€ {{ number_format($prodotto->prezzo * $item['quantita'], 2, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- Totali -->
                        <div class="border-t border-gray-200 pt-4 space-y-2">
                            <div class="flex justify-between text-base text-gray-900">
                                <span>Subtotale</span>
                                <span>€ {{ number_format($totale, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-base text-gray-900">
                                <span>Spedizione</span>
                                <span>Gratuita</span>
                            </div>
                            <div class="flex justify-between text-lg font-semibold text-gray-900 border-t border-gray-200 pt-2">
                                <span>Totale</span>
                                <span class="shop-primary-text">€ {{ number_format($totale, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Pulsante conferma ordine -->
                        <div class="mt-8">
                            <button type="submit" 
                                    class="w-full flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white shop-bg-primary hover:opacity-90 transition-opacity focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    id="submit-btn">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Conferma ordine
                            </button>
                        </div>

                        <!-- Note sulla privacy -->
                        <div class="mt-4 text-xs text-gray-500">
                            <p>I tuoi dati personali verranno utilizzati per elaborare il tuo ordine, supportare la tua esperienza su questo sito web e per altri scopi descritti nella nostra privacy policy.</p>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
// Validazione form e UX migliorata
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('submit-btn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            // Disabilita il pulsante per evitare doppi submit
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Elaborazione in corso...
            `;
        });
    }
    
    // Auto-format CAP
    const capInput = document.getElementById('cap');
    if (capInput) {
        capInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 5) {
                value = value.substring(0, 5);
            }
            e.target.value = value;
        });
    }
    
    // Auto-format telefono
    const telefonoInput = document.getElementById('telefono');
    if (telefonoInput) {
        telefonoInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });
    }
});
</script>
@endsection