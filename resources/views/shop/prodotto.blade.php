@extends('shop.layout')

@section('title', $prodotto->nome . ' - ' . $shop->nome)
@section('meta_description', $prodotto->descrizione ?? 'Acquista ' . $prodotto->nome . ' su ' . $shop->nome)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li>
                <a href="{{ route('shop.index', $shop->slug) }}" class="hover:text-primary transition-colors">
                    {{ $shop->nome }}
                </a>
            </li>
            <li>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </li>
            <li class="text-gray-900 font-medium">{{ $prodotto->nome }}</li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Immagini prodotto -->
        <div class="space-y-4">
            <!-- Immagine principale -->
            <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                @if($prodotto->immagine_copertina)
                    <img id="immagine-principale" 
                         src="{{ Storage::url($prodotto->immagine_copertina) }}" 
                         alt="{{ $prodotto->nome }}" 
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Galleria immagini -->
            @if($prodotto->immagini && count($prodotto->immagini) > 0)
                <div class="grid grid-cols-4 gap-2">
                    @if($prodotto->immagine_copertina)
                        <button onclick="cambiaImmagine('{{ Storage::url($prodotto->immagine_copertina) }}')"
                                class="aspect-square bg-gray-100 rounded-lg overflow-hidden border-2 border-primary">
                            <img src="{{ Storage::url($prodotto->immagine_copertina) }}" 
                                 alt="{{ $prodotto->nome }}" 
                                 class="w-full h-full object-cover">
                        </button>
                    @endif
                    @foreach($prodotto->immagini as $immagine)
                        <button onclick="cambiaImmagine('{{ Storage::url($immagine) }}')"
                                class="aspect-square bg-gray-100 rounded-lg overflow-hidden border-2 border-transparent hover:border-primary transition-colors">
                            <img src="{{ Storage::url($immagine) }}" 
                                 alt="{{ $prodotto->nome }}" 
                                 class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Dettagli prodotto -->
        <div class="space-y-6">
            <!-- Titolo e prezzo -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $prodotto->nome }}</h1>
                @if($prodotto->tipo)
                    <span class="inline-block bg-gray-100 text-gray-600 text-sm px-3 py-1 rounded-full mb-4">
                        {{ ucfirst($prodotto->tipo) }}
                    </span>
                @endif
                <div class="text-3xl font-bold text-primary mb-4">
                    €{{ number_format($prodotto->prezzo, 2, ',', '.') }}
                </div>
            </div>

            <!-- Disponibilità -->
            <div class="flex items-center space-x-2">
                @if($prodotto->quantita_disponibile > 0)
                    <div class="flex items-center text-green-600">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">
                            @if($prodotto->quantita_disponibile <= 5)
                                Solo {{ $prodotto->quantita_disponibile }} disponibili
                            @else
                                Disponibile
                            @endif
                        </span>
                    </div>
                @else
                    <div class="flex items-center text-red-600">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">Esaurito</span>
                    </div>
                @endif
            </div>

            <!-- Descrizione -->
            @if($prodotto->descrizione)
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Descrizione</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $prodotto->descrizione }}</p>
                </div>
            @endif

            <!-- Caratteristiche fisiche -->
            @if($prodotto->peso || $prodotto->lunghezza || $prodotto->larghezza || $prodotto->altezza)
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Caratteristiche</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        @if($prodotto->peso)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Peso:</span>
                                <span class="font-medium">{{ $prodotto->peso }} kg</span>
                            </div>
                        @endif
                        @if($prodotto->lunghezza)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Lunghezza:</span>
                                <span class="font-medium">{{ $prodotto->lunghezza }} cm</span>
                            </div>
                        @endif
                        @if($prodotto->larghezza)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Larghezza:</span>
                                <span class="font-medium">{{ $prodotto->larghezza }} cm</span>
                            </div>
                        @endif
                        @if($prodotto->altezza)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Altezza:</span>
                                <span class="font-medium">{{ $prodotto->altezza }} cm</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Quantità e aggiungi al carrello -->
            @if($prodotto->quantita_disponibile > 0)
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <label for="quantita" class="text-sm font-medium text-gray-700">Quantità:</label>
                        <div class="flex items-center border border-gray-300 rounded-lg">
                            <button type="button" onclick="decrementaQuantita()" 
                                    class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-l-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            <input type="number" id="quantita" value="1" min="1" max="{{ $prodotto->quantita_disponibile }}"
                                   class="w-16 py-2 text-center border-0 focus:ring-0 focus:outline-none">
                            <button type="button" onclick="incrementaQuantita()" 
                                    class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-r-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button onclick="aggiungiAlCarrelloConQuantita()" 
                            class="w-full bg-primary text-white py-3 px-6 rounded-lg font-semibold hover:bg-primary-dark transition-colors flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 10-4 0v4.01"></path>
                        </svg>
                        <span>Aggiungi al carrello</span>
                    </button>
                </div>
            @else
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-red-800 font-medium">Prodotto attualmente esaurito</span>
                    </div>
                </div>
            @endif

            <!-- Informazioni aggiuntive -->
            @if($prodotto->fornitore)
                <div class="border-t pt-4">
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">Fornitore:</span> {{ $prodotto->fornitore->nome }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Funzione per cambiare l'immagine principale
function cambiaImmagine(src) {
    document.getElementById('immagine-principale').src = src;
    
    // Aggiorna i bordi dei thumbnail
    document.querySelectorAll('[onclick*="cambiaImmagine"]').forEach(btn => {
        btn.classList.remove('border-primary');
        btn.classList.add('border-transparent');
    });
    
    // Aggiungi il bordo al thumbnail selezionato
    event.target.closest('button').classList.remove('border-transparent');
    event.target.closest('button').classList.add('border-primary');
}

// Funzioni per gestire la quantità
function incrementaQuantita() {
    const input = document.getElementById('quantita');
    const max = parseInt(input.getAttribute('max'));
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decrementaQuantita() {
    const input = document.getElementById('quantita');
    const min = parseInt(input.getAttribute('min'));
    const current = parseInt(input.value);
    if (current > min) {
        input.value = current - 1;
    }
}

// Funzione per aggiungere al carrello con quantità
function aggiungiAlCarrelloConQuantita() {
    const quantita = parseInt(document.getElementById('quantita').value);
    
    fetch(`{{ route('shop.carrello.aggiungi', $shop->slug) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            prodotto_id: {{ $prodotto->id }},
            quantita: quantita
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Aggiorna il conteggio del carrello
            updateCartCount(data.carrello_count);
            
            // Mostra il modale di conferma
            document.getElementById('product-added-modal').classList.remove('hidden');
            document.getElementById('product-added-modal').classList.add('flex');
        } else {
            alert(data.message || 'Errore durante l\'aggiunta al carrello');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        alert('Errore durante l\'aggiunta al carrello');
    });
}
</script>
@endpush