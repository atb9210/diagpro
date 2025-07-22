<?php

namespace App\Http\Controllers;

use App\Models\Ordini;
use App\Models\Prodotto;
use App\Models\Shop;
use App\Models\Cliente;
use App\Models\RichiestaOrdine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    /**
     * Mostra la homepage di un Mini Shop
     */
    public function index($slug)
    {
        $shop = Shop::where('slug', $slug)
            ->where('attivo', true)
            ->firstOrFail();
            
        // Filtro per tipo se specificato
        $query = $shop->prodotti()->where('stato', 'attivo');
        
        if (request('tipo')) {
            $query->where('tipo', request('tipo'));
        }
        
        $prodotti = $query->orderBy('created_at', 'desc')->paginate(12);
        
        // Ottieni tutti i tipi di prodotti disponibili per questo shop
        $tipiProdotti = $shop->prodotti()
            ->where('stato', 'attivo')
            ->distinct()
            ->pluck('tipo')
            ->filter()
            ->sort()
            ->values();
            
        return view('shop.index', compact('shop', 'prodotti', 'tipiProdotti'));
    }
    
    /**
     * Mostra la pagina di un singolo prodotto
     */
    public function prodotto($slug, $prodottoId)
    {
        $shop = Shop::where('slug', $slug)
            ->where('attivo', true)
            ->firstOrFail();
            
        $prodotto = $shop->prodotti()
            ->where('prodottos.id', $prodottoId)
            ->where('prodottos.stato', 'attivo')
            ->firstOrFail();
            
        return view('shop.prodotto', compact('shop', 'prodotto'));
    }
    
    /**
     * Aggiunge un prodotto al carrello
     */
    public function aggiungiAlCarrello(Request $request, $slug)
    {
        $shop = Shop::where('slug', $slug)
            ->where('attivo', true)
            ->firstOrFail();
            
        $validator = Validator::make($request->all(), [
            'prodotto_id' => 'required|exists:prodottos,id',
            'quantita' => 'required|integer|min:1'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dati non validi'
            ], 422);
        }
        
        $prodotto = Prodotto::where('id', $request->prodotto_id)
            ->where('shop_id', $shop->id)
            ->where('stato', 'attivo')
            ->firstOrFail();
            
        // Verifica disponibilità
        if ($prodotto->tipo === 'fisico' && $prodotto->quantita_disponibile < $request->quantita) {
            return response()->json([
                'success' => false,
                'message' => 'Quantità non disponibile'
            ], 422);
        }
        
        // Gestione carrello in sessione
        $carrello = Session::get("carrello_{$shop->id}", []);
        
        $prodottoKey = $prodotto->id;
        if (isset($carrello[$prodottoKey])) {
            $carrello[$prodottoKey]['quantita'] += $request->quantita;
        } else {
            $carrello[$prodottoKey] = [
                'id' => $prodotto->id,
                'nome' => $prodotto->nome,
                'prezzo' => $prodotto->prezzo,
                'quantita' => $request->quantita,
                'immagine' => $prodotto->immagine_copertina
            ];
        }
        
        Session::put("carrello_{$shop->id}", $carrello);
        
        return response()->json([
            'success' => true,
            'message' => 'Prodotto aggiunto al carrello',
            'carrello_count' => array_sum(array_column($carrello, 'quantita'))
        ]);
    }
    
    /**
     * Mostra il carrello
     */
    public function carrello($slug)
    {
        $shop = Shop::where('slug', $slug)
            ->where('attivo', true)
            ->firstOrFail();
            
        $carrello = Session::get("carrello_{$shop->id}", []);
        $totale = array_sum(array_map(fn($item) => $item['prezzo'] * $item['quantita'], $carrello));
        
        return view('shop.carrello', compact('shop', 'carrello', 'totale'));
    }
    
    /**
     * Ottieni il conteggio del carrello
     */
    public function conteggioCarrello($slug)
    {
        $shop = Shop::where('slug', $slug)
            ->where('attivo', true)
            ->firstOrFail();
            
        $carrello = Session::get("carrello_{$shop->id}", []);
        $conteggio = array_sum(array_column($carrello, 'quantita'));
        
        return response()->json([
            'carrello_count' => $conteggio
        ]);
    }
    
    /**
     * Aggiorna la quantità di un prodotto nel carrello
     */
    public function aggiornaCarrello(Request $request, $slug)
    {
        $shop = Shop::where('slug', $slug)
            ->where('attivo', true)
            ->firstOrFail();
            
        $validator = Validator::make($request->all(), [
            'prodotto_id' => 'required|integer',
            'quantita' => 'required|integer|min:0'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dati non validi'], 422);
        }
        
        $carrello = Session::get("carrello_{$shop->id}", []);
        
        if ($request->quantita == 0) {
            unset($carrello[$request->prodotto_id]);
        } else {
            if (isset($carrello[$request->prodotto_id])) {
                $carrello[$request->prodotto_id]['quantita'] = $request->quantita;
            }
        }
        
        Session::put("carrello_{$shop->id}", $carrello);
        
        $totale = array_sum(array_map(fn($item) => $item['prezzo'] * $item['quantita'], $carrello));
        
        return response()->json([
            'success' => true,
            'totale' => number_format($totale, 2, ',', '.'),
            'carrello_count' => array_sum(array_column($carrello, 'quantita'))
        ]);
    }
    
    /**
     * Mostra la pagina di checkout
     */
    public function checkout($slug)
    {
        $shop = Shop::where('slug', $slug)
            ->where('attivo', true)
            ->firstOrFail();
            
        $carrello = Session::get("carrello_{$shop->id}", []);
        
        if (empty($carrello)) {
            return redirect()->route('shop.index', $slug)
                ->with('error', 'Il carrello è vuoto');
        }
        
        $totale = array_sum(array_map(fn($item) => $item['prezzo'] * $item['quantita'], $carrello));
        
        return view('shop.checkout', compact('shop', 'carrello', 'totale'));
    }
    
    /**
     * Processa la richiesta d'ordine
     */
    public function processaOrdine(Request $request, $slug)
    {
        $shop = Shop::where('slug', $slug)
            ->where('attivo', true)
            ->firstOrFail();
            
        $carrello = Session::get("carrello_{$shop->id}", []);
        
        if (empty($carrello)) {
            return redirect()->route('shop.index', $slug)
                ->with('error', 'Il carrello è vuoto');
        }
        
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'indirizzo' => 'required|string|max:500',
            'citta' => 'required|string|max:100',
            'cap' => 'required|string|max:10',
            'provincia' => 'required|string|max:2',
            'nazione' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:1000'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        DB::transaction(function () use ($request, $shop, $carrello, &$richiestaOrdine) {
            // Crea o trova il cliente
            $cliente = Cliente::firstOrCreate(
                ['email' => $request->email],
                [
                    'nome' => $request->nome,
                    'telefono' => $request->telefono,
                    'indirizzo' => $request->indirizzo,
                    'citta' => $request->citta,
                    'cap' => $request->cap,
                    'provincia' => $request->provincia
                ]
            );
            
            // Calcola totale
            $totale = array_sum(array_map(fn($item) => $item['prezzo'] * $item['quantita'], $carrello));
            
            // Prepara i dati cliente e spedizione
            $datiCliente = [
                'nome' => $request->nome,
                'email' => $request->email,
                'telefono' => $request->telefono
            ];
            
            $datiSpedizione = [
                'indirizzo' => $request->indirizzo,
                'citta' => $request->citta,
                'cap' => $request->cap,
                'provincia' => $request->provincia,
                'nazione' => $request->nazione ?? 'Italia'
            ];
            
            // Crea la richiesta d'ordine
            $richiestaOrdine = RichiestaOrdine::create([
                'shop_id' => $shop->id,
                'cliente_id' => $cliente->id,
                'totale' => $totale,
                'totale_spedizione' => 0, // Da calcolare in base alle regole del shop
                'note' => $request->note,
                'dati_cliente' => $datiCliente,
                'dati_spedizione' => $datiSpedizione,
                'stato' => RichiestaOrdine::STATO_IN_ATTESA,
                'ip_origine' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadati_aggiuntivi' => [
                    'shop_nome' => $shop->nome,
                    'timestamp_checkout' => now()->toISOString(),
                    'carrello_originale' => $carrello
                ]
            ]);
            
            // Associa i prodotti alla richiesta d'ordine
            foreach ($carrello as $prodottoId => $item) {
                $prodotto = Prodotto::find($prodottoId);
                if ($prodotto) {
                    $richiestaOrdine->prodotti()->attach($prodotto->id, [
                        'nome_prodotto' => $prodotto->nome,
                        'sku' => $prodotto->sku,
                        'prezzo_unitario' => $prodotto->prezzo,
                        'prezzo_personalizzato' => $item['prezzo'], // Prezzo al momento dell'ordine
                        'quantita' => $item['quantita'],
                        'subtotale' => $item['prezzo'] * $item['quantita'],
                        'configurazione' => json_encode([
                            'tipo' => $prodotto->tipo,
                            'categoria' => $prodotto->categoria?->nome
                        ])
                    ]);
                }
            }
        });
        
        // Svuota il carrello
        Session::forget("carrello_{$shop->id}");
        
        return redirect()->route('shop.conferma', [$slug, $richiestaOrdine->id])
            ->with('success', 'Richiesta d\'ordine inviata con successo! Riceverai una conferma via email una volta validata.');
    }
    
    /**
     * Mostra la pagina di conferma richiesta ordine
     */
    public function confermaOrdine($slug, $richiestaId)
    {
        $shop = Shop::where('slug', $slug)
            ->where('attivo', true)
            ->firstOrFail();
            
        $richiesta = RichiestaOrdine::where('id', $richiestaId)
            ->where('shop_id', $shop->id)
            ->with(['cliente', 'prodotti'])
            ->firstOrFail();
            
        return view('shop.conferma', compact('shop', 'richiesta'));
    }
}
