<?php

namespace Database\Seeders;

use App\Models\RichiestaOrdine;
use App\Models\Shop;
use App\Models\Cliente;
use App\Models\Prodotto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RichiesteOrdineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verifica che esistano shop, clienti e prodotti
        $shop = Shop::first();
        $cliente = Cliente::first();
        $prodotti = Prodotto::take(3)->get();
        
        if (!$shop || !$cliente || $prodotti->count() < 2) {
            $this->command->warn('Assicurati di avere almeno 1 shop, 1 cliente e 2 prodotti nel database prima di eseguire questo seeder.');
            return;
        }
        
        // Crea alcune richieste ordine di esempio
        $richieste = [
            [
                'stato' => RichiestaOrdine::STATO_IN_ATTESA,
                'totale' => 150.00,
                'prodotti' => $prodotti->take(2),
            ],
            [
                'stato' => RichiestaOrdine::STATO_IN_VALIDAZIONE,
                'totale' => 89.50,
                'prodotti' => $prodotti->skip(1)->take(1),
            ],
            [
                'stato' => RichiestaOrdine::STATO_APPROVATO,
                'totale' => 245.75,
                'prodotti' => $prodotti,
                'note_validazione' => 'Richiesta approvata dopo verifica disponibilità prodotti.',
            ],
        ];
        
        foreach ($richieste as $index => $richiestaData) {
            DB::transaction(function () use ($richiestaData, $shop, $cliente, $index) {
                // Crea la richiesta ordine
                $richiesta = RichiestaOrdine::create([
                    'numero_richiesta' => 'RQ-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                    'shop_id' => $shop->id,
                    'cliente_id' => $cliente->id,
                    'totale' => $richiestaData['totale'],
                    'totale_spedizione' => 10.00,
                    'stato' => $richiestaData['stato'],
                    'note' => 'Richiesta di esempio generata dal seeder.',
                    'note_validazione' => $richiestaData['note_validazione'] ?? null,
                    
                    // Snapshot dati cliente (JSON)
                    'dati_cliente' => [
                        'nome' => $cliente->nome,
                        'email' => $cliente->email,
                        'telefono' => $cliente->telefono,
                        'ragione_sociale' => $cliente->ragione_sociale,
                        'codice_fiscale' => $cliente->codice_fiscale,
                        'partita_iva' => $cliente->partita_iva,
                    ],
                    
                    // Snapshot dati spedizione (JSON)
                    'dati_spedizione' => [
                        'nome' => $cliente->nome,
                        'indirizzo' => $cliente->indirizzo_spedizione ?? 'Via Roma 123',
                        'cap' => $cliente->cap_spedizione ?? '00100',
                        'citta' => $cliente->citta_spedizione ?? 'Roma',
                        'provincia' => $cliente->provincia_spedizione ?? 'RM',
                        'stato' => $cliente->stato_spedizione ?? 'Italia',
                    ],
                    
                    // Metadati richiesta
                    'ip_origine' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Test Browser)',
                    'metadati_aggiuntivi' => [
                        'referrer' => url('/shop/' . $shop->slug),
                        'session_id' => 'test_session_' . uniqid(),
                    ],
                    
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
                
                // Aggiungi i prodotti alla richiesta
                foreach ($richiestaData['prodotti'] as $prodotto) {
                    $quantita = rand(1, 3);
                    $prezzoUnitario = $prodotto->prezzo_vendita ?? 10.00; // Fallback se prezzo null
                    
                    $richiesta->prodotti()->attach($prodotto->id, [
                        'quantita' => $quantita,
                        'prezzo_unitario' => $prezzoUnitario,
                        'prezzo_personalizzato' => null,
                        'subtotale' => $prezzoUnitario * $quantita,
                        
                        // Snapshot prodotto
                        'nome_prodotto' => $prodotto->nome,
                        'sku' => $prodotto->sku,
                        
                        'configurazione' => json_encode([
                            'colore' => 'Blu',
                            'taglia' => 'M',
                        ]),
                    ]);
                }
            });
        }
        
        $this->command->info('Richieste ordine di esempio create con successo!');
    }
}