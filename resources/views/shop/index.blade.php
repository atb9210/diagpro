@extends('shop.layout')

@section('title', $shop->nome)
@section('meta_description', $shop->meta_description ?? 'Scopri i prodotti di ' . $shop->nome)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header del negozio -->
    <div class="text-center mb-12">
        @if($shop->logo)
            <img src="{{ Storage::url($shop->logo) }}" alt="{{ $shop->nome }}" class="mx-auto mb-4 h-24 w-auto">
        @endif
        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $shop->nome }}</h1>
        @if($shop->descrizione)
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ $shop->descrizione }}</p>
        @endif
    </div>

    <!-- Filtri prodotti -->
    <div class="mb-8">
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="{{ route('shop.index', $shop->slug) }}" 
               class="px-4 py-2 rounded-lg {{ !request('tipo') ? 'bg-primary text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition-colors">
                Tutti i prodotti
            </a>
            @foreach($tipiProdotti as $tipo)
                <a href="{{ route('shop.index', $shop->slug) }}?tipo={{ $tipo }}" 
                   class="px-4 py-2 rounded-lg {{ request('tipo') === $tipo ? 'bg-primary text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition-colors">
                    {{ ucfirst($tipo) }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Griglia prodotti -->
    @if($prodotti->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($prodotti as $prodotto)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <!-- Immagine prodotto -->
                    <div class="aspect-square bg-gray-100 relative">
                        @if($prodotto->immagine_copertina)
                            <img src="{{ Storage::url($prodotto->immagine_copertina) }}" 
                                 alt="{{ $prodotto->nome }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        @endif
                        
                        <!-- Badge disponibilità -->
                        @if($prodotto->quantita_disponibile <= 0)
                            <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-semibold">
                                Esaurito
                            </div>
                        @elseif($prodotto->quantita_disponibile <= 5)
                            <div class="absolute top-2 right-2 bg-orange-500 text-white px-2 py-1 rounded text-xs font-semibold">
                                Ultimi {{ $prodotto->quantita_disponibile }}
                            </div>
                        @endif
                    </div>

                    <!-- Contenuto prodotto -->
                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-gray-900 mb-2 line-clamp-2">
                            <a href="{{ route('shop.prodotto', ['slug' => $shop->slug, 'prodotto' => $prodotto->id]) }}" 
                               class="hover:text-primary transition-colors">
                                {{ $prodotto->nome }}
                            </a>
                        </h3>
                        
                        @if($prodotto->tipo)
                            <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded mb-2">
                                {{ ucfirst($prodotto->tipo) }}
                            </span>
                        @endif

                        <div class="flex items-center justify-between">
                            <div class="text-xl font-bold text-primary">
                                €{{ number_format($prodotto->prezzo, 2, ',', '.') }}
                            </div>
                            
                            @if($prodotto->quantita_disponibile > 0)
                                <button onclick="aggiungiAlCarrello({{ $prodotto->id }}, '{{ $prodotto->nome }}', {{ $prodotto->prezzo }})" 
                                        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors text-sm font-semibold">
                                    Aggiungi
                                </button>
                            @else
                                <button disabled class="bg-gray-300 text-gray-500 px-4 py-2 rounded-lg text-sm font-semibold cursor-not-allowed">
                                    Esaurito
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginazione -->
        @if($prodotti->hasPages())
            <div class="mt-8">
                {{ $prodotti->links() }}
            </div>
        @endif
    @else
        <!-- Nessun prodotto trovato -->
        <div class="text-center py-12">
            <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-4m-12 0H4m8 0V9"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nessun prodotto disponibile</h3>
            <p class="text-gray-500">
                @if(request('tipo'))
                    Non ci sono prodotti di tipo "{{ request('tipo') }}" al momento.
                @else
                    Non ci sono prodotti disponibili al momento.
                @endif
            </p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Funzione per aggiungere prodotto al carrello
function aggiungiAlCarrello(prodottoId, nome, prezzo) {
    fetch(`{{ route('shop.carrello.aggiungi', $shop->slug) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            prodotto_id: prodottoId,
            quantita: 1
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