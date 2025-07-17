<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dettaglio Ordine') }} #{{ $ordini->id }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('ordini.edit', $ordini->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition">Modifica</a>
                <form action="{{ route('ordini.destroy', $ordini->id) }}" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questo ordine?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">Elimina</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Informazioni Principali -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Informazioni Principali</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <span class="font-semibold">Cliente:</span>
                                <p>{{ $ordini->cliente->nome }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Traffic Source:</span>
                                <p>{{ $ordini->trafficSource->nome ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Data:</span>
                                <p>{{ $ordini->data->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Tipo Vendita:</span>
                                <p class="capitalize">{{ $ordini->tipo_vendita }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Link Ordine:</span>
                                <p>{{ $ordini->link_ordine ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">IVA applicata:</span>
                                <p>{{ $ordini->vat ? 'Sì' : 'No' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Prodotti -->
                    @if($ordini->prodotti->isNotEmpty())
                    <div class="mb-8">
                        <h3 class="text-lg font-medium mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Prodotti</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantità</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prezzo Unitario</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subtotale</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($ordini->prodotti as $prodotto)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $prodotto->nome }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $prodotto->pivot->quantita }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">€ {{ number_format($prodotto->pivot->prezzo_unitario, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">€ {{ number_format($prodotto->pivot->quantita * $prodotto->pivot->prezzo_unitario, 2, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Abbonamenti -->
                    @if($ordini->abbonamenti->isNotEmpty())
                    <div class="mb-8">
                        <h3 class="text-lg font-medium mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Abbonamenti</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data Inizio</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data Fine</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prezzo</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($ordini->abbonamenti as $abbonamento)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $abbonamento->nome }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $abbonamento->pivot->data_inizio ? \Carbon\Carbon::parse($abbonamento->pivot->data_inizio)->format('d/m/Y') : 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $abbonamento->pivot->data_fine ? \Carbon\Carbon::parse($abbonamento->pivot->data_fine)->format('d/m/Y') : 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">€ {{ number_format($abbonamento->pivot->prezzo, 2, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Spedizione -->
                    @if($ordini->spedizione)
                    <div class="mb-8">
                        <h3 class="text-lg font-medium mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Spedizione</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <span class="font-semibold">Corriere:</span>
                                <p>{{ $ordini->spedizione->corriere ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Numero Tracciamento:</span>
                                <p>{{ $ordini->spedizione->numero_tracciamento ?? 'N/A' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <span class="font-semibold">Indirizzo Spedizione:</span>
                                <p>{{ $ordini->spedizione->indirizzo_spedizione }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Costi e Margine -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Costi e Margine</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <span class="font-semibold">Prezzo di Vendita:</span>
                                <p>€ {{ number_format($ordini->prezzo_vendita, 2, ',', '.') }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Costo Marketing:</span>
                                <p>€ {{ number_format($ordini->costo_marketing, 2, ',', '.') }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Costo Prodotto:</span>
                                <p>€ {{ number_format($ordini->costo_prodotto, 2, ',', '.') }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Costo Spedizione:</span>
                                <p>€ {{ number_format($ordini->costo_spedizione, 2, ',', '.') }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Altri Costi:</span>
                                <p>€ {{ number_format($ordini->altri_costi, 2, ',', '.') }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Margine:</span>
                                <p class="font-bold {{ $ordini->margine >= 0 ? 'text-green-500' : 'text-red-500' }}">€ {{ number_format($ordini->margine, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Note -->
                    @if($ordini->note)
                    <div class="mb-8">
                        <h3 class="text-lg font-medium mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Note</h3>
                        <p>{{ $ordini->note }}</p>
                    </div>
                    @endif

                    <div class="flex justify-end">
                        <a href="{{ route('ordini.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">Torna alla Lista</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>