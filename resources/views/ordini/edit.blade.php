<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Modifica Ordine') }} #{{ $ordini->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('ordini.update', $ordini->id) }}" method="POST" id="ordineForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Informazioni Principali -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Informazioni Principali</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Cliente -->
                                <div>
                                    <label for="cliente_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente *</label>
                                    <select id="cliente_id" name="cliente_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Seleziona un cliente</option>
                                        @foreach($clienti as $cliente)
                                            <option value="{{ $cliente->id }}" {{ old('cliente_id', $ordini->cliente_id) == $cliente->id ? 'selected' : '' }}>{{ $cliente->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Traffic Source -->
                                <div>
                                    <label for="traffic_source_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Traffic Source</label>
                                    <select id="traffic_source_id" name="traffic_source_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Seleziona una traffic source</option>
                                        @foreach($trafficSources as $trafficSource)
                                            <option value="{{ $trafficSource->id }}" {{ old('traffic_source_id', $ordini->traffic_source_id) == $trafficSource->id ? 'selected' : '' }}>{{ $trafficSource->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Data -->
                                <div>
                                    <label for="data" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data *</label>
                                    <input type="date" id="data" name="data" value="{{ old('data', $ordini->data->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                
                                <!-- Tipo Vendita -->
                                <div>
                                    <label for="tipo_vendita" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo Vendita *</label>
                                    <select id="tipo_vendita" name="tipo_vendita" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="online" {{ old('tipo_vendita', $ordini->tipo_vendita) == 'online' ? 'selected' : '' }}>Online</option>
                                        <option value="appuntamento" {{ old('tipo_vendita', $ordini->tipo_vendita) == 'appuntamento' ? 'selected' : '' }}>Appuntamento</option>
                                        <option value="contrassegno" {{ old('tipo_vendita', $ordini->tipo_vendita) == 'contrassegno' ? 'selected' : '' }}>Contrassegno</option>
                                    </select>
                                </div>
                                
                                <!-- Link Ordine -->
                                <div>
                                    <label for="link_ordine" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Link Ordine</label>
                                    <input type="text" id="link_ordine" name="link_ordine" value="{{ old('link_ordine', $ordini->link_ordine) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Es. link ordine eBay">
                                </div>
                                
                                <!-- VAT -->
                                <div class="flex items-center mt-6">
                                    <input type="checkbox" id="vat" name="vat" value="1" {{ old('vat', $ordini->vat) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="vat" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">IVA applicata</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Prodotti -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Prodotti *</h3>
                            
                            <div id="prodotti-container">
                                @forelse($ordini->prodotti as $index => $prodotto)
                                <div class="prodotto-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md mb-4">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prodotto *</label>
                                            <select name="prodotti[{{ $index }}][id]" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                <option value="">Seleziona un prodotto</option>
                                                @foreach($prodotti as $p)
                                                    <option value="{{ $p->id }}" data-prezzo="{{ $p->prezzo }}" {{ $p->id == $prodotto->id ? 'selected' : '' }}>{{ $p->nome }} - € {{ number_format($p->prezzo, 2, ',', '.') }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantità *</label>
                                            <input type="number" name="prodotti[{{ $index }}][quantita]" value="{{ $prodotto->pivot->quantita }}" min="1" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prezzo Unitario (€) *</label>
                                            <input type="number" name="prodotti[{{ $index }}][prezzo_unitario]" value="{{ $prodotto->pivot->prezzo_unitario }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        </div>
                                        <div class="flex items-end">
                                            <button type="button" class="remove-prodotto px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition" @if($ordini->prodotti->count() <= 1) style="display: none;" @endif>Rimuovi</button>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="prodotto-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md mb-4">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prodotto *</label>
                                            <select name="prodotti[0][id]" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                <option value="">Seleziona un prodotto</option>
                                                @foreach($prodotti as $p)
                                                    <option value="{{ $p->id }}" data-prezzo="{{ $p->prezzo }}">{{ $p->nome }} - € {{ number_format($p->prezzo, 2, ',', '.') }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantità *</label>
                                            <input type="number" name="prodotti[0][quantita]" value="1" min="1" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prezzo Unitario (€) *</label>
                                            <input type="number" name="prodotti[0][prezzo_unitario]" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        </div>
                                        <div class="flex items-end">
                                            <button type="button" class="remove-prodotto px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition" style="display: none;">Rimuovi</button>
                                        </div>
                                    </div>
                                </div>
                                @endforelse
                            </div>
                            
                            <button type="button" id="add-prodotto" class="mt-2 px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition">+ Aggiungi Prodotto</button>
                        </div>
                        
                        <!-- Abbonamenti -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Abbonamenti</h3>
                            
                            <div id="abbonamenti-container">
                                @foreach($ordini->abbonamenti as $index => $abbonamento)
                                <div class="abbonamento-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md mb-4">
                                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Abbonamento *</label>
                                            <select name="abbonamenti[{{ $index }}][id]" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                <option value="">Seleziona un abbonamento</option>
                                                @foreach($abbonamenti as $a)
                                                    <option value="{{ $a->id }}" data-prezzo="{{ $a->prezzo }}" {{ $a->id == $abbonamento->id ? 'selected' : '' }}>{{ $a->nome }} - € {{ number_format($a->prezzo, 2, ',', '.') }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data Inizio *</label>
                                            <input type="date" name="abbonamenti[{{ $index }}][data_inizio]" value="{{ $abbonamento->pivot->data_inizio ? \Carbon\Carbon::parse($abbonamento->pivot->data_inizio)->format('Y-m-d') : '' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data Fine</label>
                                            <input type="date" name="abbonamenti[{{ $index }}][data_fine]" value="{{ $abbonamento->pivot->data_fine ? \Carbon\Carbon::parse($abbonamento->pivot->data_fine)->format('Y-m-d') : '' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prezzo (€) *</label>
                                            <input type="number" name="abbonamenti[{{ $index }}][prezzo]" value="{{ $abbonamento->pivot->prezzo }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        </div>
                                        <div class="flex items-end">
                                            <button type="button" class="remove-abbonamento px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">Rimuovi</button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <button type="button" id="add-abbonamento" class="mt-2 px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition">+ Aggiungi Abbonamento</button>
                        </div>
                        
                        <!-- Spedizione -->
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <input type="checkbox" id="has-spedizione" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" @if($ordini->spedizione) checked @endif>
                                <h3 class="ml-2 text-lg font-medium">Spedizione</h3>
                            </div>
                            
                            <div id="spedizione-container" class="@if(!$ordini->spedizione) hidden @endif bg-gray-50 dark:bg-gray-700 p-4 rounded-md">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="spedizione[corriere]" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Corriere</label>
                                        <input type="text" id="spedizione[corriere]" name="spedizione[corriere]" value="{{ old('spedizione.corriere', $ordini->spedizione->corriere ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    
                                    <div>
                                        <label for="spedizione[numero_tracciamento]" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Numero Tracciamento</label>
                                        <input type="text" id="spedizione[numero_tracciamento]" name="spedizione[numero_tracciamento]" value="{{ old('spedizione.numero_tracciamento', $ordini->spedizione->numero_tracciamento ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label for="spedizione[indirizzo_spedizione]" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Indirizzo Spedizione *</label>
                                        <textarea id="spedizione[indirizzo_spedizione]" name="spedizione[indirizzo_spedizione]" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('spedizione.indirizzo_spedizione', $ordini->spedizione->indirizzo_spedizione ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Costi e Margine -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Costi e Margine</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="prezzo_vendita" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prezzo di Vendita (€) *</label>
                                    <input type="number" id="prezzo_vendita" name="prezzo_vendita" value="{{ old('prezzo_vendita', $ordini->prezzo_vendita) }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                
                                <div>
                                    <label for="costo_marketing" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Costo Marketing (€)</label>
                                    <input type="number" id="costo_marketing" name="costo_marketing" value="{{ old('costo_marketing', $ordini->costo_marketing) }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div>
                                    <label for="costo_prodotto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Costo Prodotto (€)</label>
                                    <input type="number" id="costo_prodotto" name="costo_prodotto" value="{{ old('costo_prodotto', $ordini->costo_prodotto) }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div>
                                    <label for="costo_spedizione" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Costo Spedizione (€)</label>
                                    <input type="number" id="costo_spedizione" name="costo_spedizione" value="{{ old('costo_spedizione', $ordini->costo_spedizione) }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div>
                                    <label for="altri_costi" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Altri Costi (€)</label>
                                    <input type="number" id="altri_costi" name="altri_costi" value="{{ old('altri_costi', $ordini->altri_costi) }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div>
                                    <label for="margine" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Margine (€)</label>
                                    <input type="number" id="margine" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm bg-gray-100 dark:bg-gray-800" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Note -->
                        <div class="mb-8">
                            <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Note</label>
                            <textarea id="note" name="note" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('note', $ordini->note) }}</textarea>
                        </div>
                        
                        <!-- Pulsanti -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('ordini.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">Annulla</a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">Salva Modifiche</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestione prodotti
            let prodottoIndex = {{ $ordini->prodotti->count() }};
            const prodottiContainer = document.getElementById('prodotti-container');
            const addProdottoBtn = document.getElementById('add-prodotto');
            
            // Aggiorna il prezzo unitario quando si seleziona un prodotto
            prodottiContainer.addEventListener('change', function(e) {
                if (e.target.tagName === 'SELECT' && e.target.name.includes('[id]')) {
                    const selectedOption = e.target.options[e.target.selectedIndex];
                    const prezzo = selectedOption.dataset.prezzo;
                    const prodottoItem = e.target.closest('.prodotto-item');
                    const prezzoInput = prodottoItem.querySelector('input[name$="[prezzo_unitario]"]');
                    if (prezzo && prezzoInput) {
                        prezzoInput.value = prezzo;
                    }
                }
            });
            
            // Aggiungi prodotto
            addProdottoBtn.addEventListener('click', function() {
                const prodottoHTML = `
                    <div class="prodotto-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prodotto *</label>
                                <select name="prodotti[${prodottoIndex}][id]" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Seleziona un prodotto</option>
                                    @foreach($prodotti as $prodotto)
                                        <option value="{{ $prodotto->id }}" data-prezzo="{{ $prodotto->prezzo }}">{{ $prodotto->nome }} - € {{ number_format($prodotto->prezzo, 2, ',', '.') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantità *</label>
                                <input type="number" name="prodotti[${prodottoIndex}][quantita]" value="1" min="1" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prezzo Unitario (€) *</label>
                                <input type="number" name="prodotti[${prodottoIndex}][prezzo_unitario]" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div class="flex items-end">
                                <button type="button" class="remove-prodotto px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">Rimuovi</button>
                            </div>
                        </div>
                    </div>
                `;
                prodottiContainer.insertAdjacentHTML('beforeend', prodottoHTML);
                prodottoIndex++;
                
                // Mostra tutti i pulsanti di rimozione
                prodottiContainer.querySelectorAll('.remove-prodotto').forEach(btn => btn.style.display = 'block');
            });
            
            // Rimuovi prodotto
            prodottiContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-prodotto')) {
                    e.target.closest('.prodotto-item').remove();
                    
                    // Se rimane solo un prodotto, nascondi il pulsante di rimozione
                    const prodottoItems = prodottiContainer.querySelectorAll('.prodotto-item');
                    if (prodottoItems.length === 1) {
                        prodottoItems[0].querySelector('.remove-prodotto').style.display = 'none';
                    }
                }
            });
            
            // Gestione abbonamenti
            let abbonamentoIndex = {{ $ordini->abbonamenti->count() }};
            const abbonamentiContainer = document.getElementById('abbonamenti-container');
            const addAbbonamentoBtn = document.getElementById('add-abbonamento');
            
            // Aggiungi abbonamento
            addAbbonamentoBtn.addEventListener('click', function() {
                const abbonamentoHTML = `
                    <div class="abbonamento-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Abbonamento *</label>
                                <select name="abbonamenti[${abbonamentoIndex}][id]" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Seleziona un abbonamento</option>
                                    @foreach($abbonamenti as $abbonamento)
                                        <option value="{{ $abbonamento->id }}" data-prezzo="{{ $abbonamento->prezzo }}">{{ $abbonamento->nome }} - € {{ number_format($abbonamento->prezzo, 2, ',', '.') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data Inizio *</label>
                                <input type="date" name="abbonamenti[${abbonamentoIndex}][data_inizio]" value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data Fine</label>
                                <input type="date" name="abbonamenti[${abbonamentoIndex}][data_fine]" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prezzo (€) *</label>
                                <input type="number" name="abbonamenti[${abbonamentoIndex}][prezzo]" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                            <div class="flex items-end">
                                <button type="button" class="remove-abbonamento px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">Rimuovi</button>
                            </div>
                        </div>
                    </div>
                `;
                abbonamentiContainer.insertAdjacentHTML('beforeend', abbonamentoHTML);
                abbonamentoIndex++;
            });
            
            // Aggiorna il prezzo quando si seleziona un abbonamento
            abbonamentiContainer.addEventListener('change', function(e) {
                if (e.target.tagName === 'SELECT' && e.target.name.includes('[id]')) {
                    const selectedOption = e.target.options[e.target.selectedIndex];
                    const prezzo = selectedOption.dataset.prezzo;
                    const abbonamentoItem = e.target.closest('.abbonamento-item');
                    const prezzoInput = abbonamentoItem.querySelector('input[name$="[prezzo]"]');
                    if (prezzo && prezzoInput) {
                        prezzoInput.value = prezzo;
                    }
                }
            });
            
            // Rimuovi abbonamento
            abbonamentiContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-abbonamento')) {
                    e.target.closest('.abbonamento-item').remove();
                }
            });
            
            // Gestione spedizione
            const hasSpedizioneCheckbox = document.getElementById('has-spedizione');
            const spedizioneContainer = document.getElementById('spedizione-container');
            const indirizzoSpedizione = document.querySelector('textarea[name="spedizione[indirizzo_spedizione]"]');
            
            hasSpedizioneCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    spedizioneContainer.classList.remove('hidden');
                    indirizzoSpedizione.setAttribute('required', 'required');
                } else {
                    spedizioneContainer.classList.add('hidden');
                    indirizzoSpedizione.removeAttribute('required');
                }
            });
            
            // Calcolo del margine
            const prezzoVenditaInput = document.getElementById('prezzo_vendita');
            const costoMarketingInput = document.getElementById('costo_marketing');
            const costoProdottoInput = document.getElementById('costo_prodotto');
            const costoSpedizioneInput = document.getElementById('costo_spedizione');
            const altriCostiInput = document.getElementById('altri_costi');
            const margineInput = document.getElementById('margine');
            
            function calcolaMargine() {
                const prezzoVendita = parseFloat(prezzoVenditaInput.value) || 0;
                const costoMarketing = parseFloat(costoMarketingInput.value) || 0;
                const costoProdotto = parseFloat(costoProdottoInput.value) || 0;
                const costoSpedizione = parseFloat(costoSpedizioneInput.value) || 0;
                const altriCosti = parseFloat(altriCostiInput.value) || 0;
                
                const totaleCosti = costoMarketing + costoProdotto + costoSpedizione + altriCosti;
                const margine = prezzoVendita - totaleCosti;
                
                margineInput.value = margine.toFixed(2);
                
                // Cambia il colore del margine in base al valore
                if (margine < 0) {
                    margineInput.classList.add('text-red-500');
                    margineInput.classList.remove('text-green-500');
                } else {
                    margineInput.classList.add('text-green-500');
                    margineInput.classList.remove('text-red-500');
                }
            }
            
            // Calcola il margine quando i valori cambiano
            prezzoVenditaInput.addEventListener('input', calcolaMargine);
            costoMarketingInput.addEventListener('input', calcolaMargine);
            costoProdottoInput.addEventListener('input', calcolaMargine);
            costoSpedizioneInput.addEventListener('input', calcolaMargine);
            altriCostiInput.addEventListener('input', calcolaMargine);
            
            // Calcola il margine iniziale
            calcolaMargine();
        });
    </script>
</x-app-layout>