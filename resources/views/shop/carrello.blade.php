@extends('shop.layout')

@section('title', 'Carrello - ' . $shop->nome)

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Il tuo carrello</h1>
    
    @if(!empty($carrello))
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Header della tabella -->
            <div class="bg-gray-50 px-6 py-4 border-b">
                <div class="grid grid-cols-12 gap-4 font-semibold text-gray-700">
                    <div class="col-span-6">Prodotto</div>
                    <div class="col-span-2 text-center">Prezzo</div>
                    <div class="col-span-2 text-center">Quantità</div>
                    <div class="col-span-2 text-center">Totale</div>
                </div>
            </div>
            
            <!-- Prodotti nel carrello -->
            <div class="divide-y divide-gray-200">
                @foreach($carrello as $prodottoId => $item)
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-12 gap-4 items-center">
                            <!-- Prodotto -->
                            <div class="col-span-6 flex items-center space-x-4">
                                @if($item['immagine'])
                                    <img src="{{ Storage::url($item['immagine']) }}" 
                                         alt="{{ $item['nome'] }}" 
                                         class="w-16 h-16 object-cover rounded-lg">
                                @else
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $item['nome'] }}</h3>
                                    <p class="text-sm text-gray-500">ID: {{ $item['id'] }}</p>
                                </div>
                            </div>
                            
                            <!-- Prezzo -->
                            <div class="col-span-2 text-center">
                                <span class="font-semibold text-gray-900">
                                    €{{ number_format($item['prezzo'], 2, ',', '.') }}
                                </span>
                            </div>
                            
                            <!-- Quantità -->
                            <div class="col-span-2 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button onclick="aggiornaQuantita({{ $prodottoId }}, {{ $item['quantita'] - 1 }})" 
                                            class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition-colors"
                                            {{ $item['quantita'] <= 1 ? 'disabled' : '' }}>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    <span class="w-8 text-center font-semibold">{{ $item['quantita'] }}</span>
                                    <button onclick="aggiornaQuantita({{ $prodottoId }}, {{ $item['quantita'] + 1 }})" 
                                            class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Totale -->
                            <div class="col-span-2 text-center">
                                <span class="font-bold shop-primary-text">
                                €{{ number_format($item['prezzo'] * $item['quantita'], 2, ',', '.') }}
                            </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Totale carrello -->
            <div class="bg-gray-50 px-6 py-4 border-t">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-900">Totale carrello:</span>
                    <span id="totale-carrello" class="text-2xl font-bold shop-primary-text">
                        €{{ number_format($totale, 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Azioni -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-between">
            <a href="{{ route('shop.index', $shop->slug) }}" 
               class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-center">
                ← Continua con il negozio
            </a>
            
            <a href="{{ route('shop.checkout', $shop->slug) }}" 
               class="px-8 py-3 btn-shop-primary rounded-lg text-center font-semibold">
                Procedi al checkout →
            </a>
        </div>
    @else
        <!-- Carrello vuoto -->
        <div class="text-center py-12">
            <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 21a2 2 0 100-4 2 2 0 000 4zM9 21a2 2 0 100-4 2 2 0 000 4z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Il tuo carrello è vuoto</h3>
            <p class="text-gray-500 mb-6">Aggiungi alcuni prodotti per iniziare!</p>
            
            <a href="{{ route('shop.index', $shop->slug) }}" 
               class="inline-flex items-center px-6 py-3 btn-shop-primary rounded-lg font-semibold">
                Inizia a fare acquisti
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Funzione per aggiornare la quantità
function aggiornaQuantita(prodottoId, nuovaQuantita) {
    if (nuovaQuantita < 0) return;
    
    fetch(`{{ route('shop.carrello.aggiorna', $shop->slug) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            prodotto_id: prodottoId,
            quantita: nuovaQuantita
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Ricarica la pagina per aggiornare il carrello
            location.reload();
        } else {
            alert(data.message || 'Errore durante l\'aggiornamento del carrello');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        alert('Errore durante l\'aggiornamento del carrello');
    });
}
</script>
@endpush