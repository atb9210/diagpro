<?php

namespace App\Http\Controllers;

use App\Models\Ordini;
use App\Models\Cliente;
use App\Models\Prodotto;
use App\Models\Abbonamento;
use App\Models\TrafficSource;
use App\Models\Spedizione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdiniController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ordini = Ordini::with(['cliente', 'trafficSource', 'prodotti', 'abbonamenti', 'spedizione'])
                        ->orderBy('data', 'desc')
                        ->paginate(10);
        
        return view('ordini.index', compact('ordini'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clienti = Cliente::orderBy('nome')->get();
        $prodotti = Prodotto::orderBy('nome')->get();
        $abbonamenti = Abbonamento::where('attivo', true)->orderBy('nome')->get();
        $trafficSources = TrafficSource::where('attivo', true)->orderBy('nome')->get();
        
        return view('ordini.create', compact('clienti', 'prodotti', 'abbonamenti', 'trafficSources'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'traffic_source_id' => 'nullable|exists:traffic_sources,id',
            'data' => 'required|date',
            'tipo_vendita' => 'required|in:online,appuntamento,contrassegno',
            'link_ordine' => 'nullable|string|max:255',
            'prezzo_vendita' => 'required|numeric|min:0',
            'costo_marketing' => 'nullable|numeric|min:0',
            'costo_prodotto' => 'nullable|numeric|min:0',
            'costo_spedizione' => 'nullable|numeric|min:0',
            'altri_costi' => 'nullable|numeric|min:0',
            'vat' => 'boolean',
            'note' => 'nullable|string',
            'prodotti' => 'required|array|min:1',
            'prodotti.*.id' => 'required|exists:prodottos,id',
            'prodotti.*.quantita' => 'required|integer|min:1',
            'prodotti.*.prezzo_unitario' => 'required|numeric|min:0',
            'abbonamenti' => 'nullable|array',
            'abbonamenti.*.id' => 'required|exists:abbonamentos,id',
            'abbonamenti.*.data_inizio' => 'required|date',
            'abbonamenti.*.data_fine' => 'nullable|date|after:abbonamenti.*.data_inizio',
            'abbonamenti.*.prezzo' => 'required|numeric|min:0',
            'spedizione' => 'nullable|array',
            'spedizione.corriere' => 'nullable|string|max:255',
            'spedizione.numero_tracciamento' => 'nullable|string|max:255',
            'spedizione.indirizzo_spedizione' => 'required_with:spedizione|string',
        ]);
        
        // Calcola il margine
        $totaleCosti = 
            ($request->costo_marketing ?? 0) + 
            ($request->costo_prodotto ?? 0) + 
            ($request->costo_spedizione ?? 0) + 
            ($request->altri_costi ?? 0);
        $margine = $request->prezzo_vendita - $totaleCosti;
        
        DB::beginTransaction();
        
        try {
            // Crea l'ordine
            $ordine = Ordini::create([
                'cliente_id' => $request->cliente_id,
                'traffic_source_id' => $request->traffic_source_id,
                'data' => $request->data,
                'tipo_vendita' => $request->tipo_vendita,
                'link_ordine' => $request->link_ordine,
                'prezzo_vendita' => $request->prezzo_vendita,
                'costo_marketing' => $request->costo_marketing ?? 0,
                'costo_prodotto' => $request->costo_prodotto ?? 0,
                'costo_spedizione' => $request->costo_spedizione ?? 0,
                'altri_costi' => $request->altri_costi ?? 0,
                'margine' => $margine,
                'vat' => $request->vat ?? false,
                'note' => $request->note,
            ]);
            
            // Associa i prodotti all'ordine
            foreach ($request->prodotti as $prodotto) {
                $ordine->prodotti()->attach($prodotto['id'], [
                    'quantita' => $prodotto['quantita'],
                    'prezzo_unitario' => $prodotto['prezzo_unitario'],
                ]);
            }
            
            // Associa gli abbonamenti all'ordine (se presenti)
            if ($request->has('abbonamenti')) {
                foreach ($request->abbonamenti as $abbonamento) {
                    $ordine->abbonamenti()->attach($abbonamento['id'], [
                        'data_inizio' => $abbonamento['data_inizio'],
                        'data_fine' => $abbonamento['data_fine'] ?? null,
                        'prezzo' => $abbonamento['prezzo'],
                        'attivo' => true,
                    ]);
                }
            }
            
            // Crea la spedizione (se necessaria)
            if ($request->has('spedizione')) {
                $ordine->spedizione()->create([
                    'corriere' => $request->spedizione['corriere'] ?? null,
                    'numero_tracciamento' => $request->spedizione['numero_tracciamento'] ?? null,
                    'stato' => 'in_preparazione',
                    'indirizzo_spedizione' => $request->spedizione['indirizzo_spedizione'],
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('ordini.show', $ordine)
                             ->with('success', 'Ordine creato con successo!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Si è verificato un errore durante la creazione dell\'ordine: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ordini $ordini)
    {
        $ordini->load(['cliente', 'trafficSource', 'prodotti', 'abbonamenti', 'spedizione']);
        return view('ordini.show', compact('ordini'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ordini $ordini)
    {
        $ordini->load(['cliente', 'trafficSource', 'prodotti', 'abbonamenti', 'spedizione']);
        
        $clienti = Cliente::orderBy('nome')->get();
        $prodotti = Prodotto::orderBy('nome')->get();
        $abbonamenti = Abbonamento::orderBy('nome')->get();
        $trafficSources = TrafficSource::where('attivo', true)->orderBy('nome')->get();
        
        return view('ordini.edit', compact('ordini', 'clienti', 'prodotti', 'abbonamenti', 'trafficSources'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ordini $ordini)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'traffic_source_id' => 'nullable|exists:traffic_sources,id',
            'data' => 'required|date',
            'tipo_vendita' => 'required|in:online,appuntamento,contrassegno',
            'link_ordine' => 'nullable|string|max:255',
            'prezzo_vendita' => 'required|numeric|min:0',
            'costo_marketing' => 'nullable|numeric|min:0',
            'costo_prodotto' => 'nullable|numeric|min:0',
            'costo_spedizione' => 'nullable|numeric|min:0',
            'altri_costi' => 'nullable|numeric|min:0',
            'vat' => 'boolean',
            'note' => 'nullable|string',
            'prodotti' => 'required|array|min:1',
            'prodotti.*.id' => 'required|exists:prodottos,id',
            'prodotti.*.quantita' => 'required|integer|min:1',
            'prodotti.*.prezzo_unitario' => 'required|numeric|min:0',
            'abbonamenti' => 'nullable|array',
            'abbonamenti.*.id' => 'required|exists:abbonamentos,id',
            'abbonamenti.*.data_inizio' => 'required|date',
            'abbonamenti.*.data_fine' => 'nullable|date|after:abbonamenti.*.data_inizio',
            'abbonamenti.*.prezzo' => 'required|numeric|min:0',
            'spedizione' => 'nullable|array',
            'spedizione.corriere' => 'nullable|string|max:255',
            'spedizione.numero_tracciamento' => 'nullable|string|max:255',
            'spedizione.indirizzo_spedizione' => 'required_with:spedizione|string',
        ]);
        
        // Calcola il margine
        $totaleCosti = 
            ($request->costo_marketing ?? 0) + 
            ($request->costo_prodotto ?? 0) + 
            ($request->costo_spedizione ?? 0) + 
            ($request->altri_costi ?? 0);
        $margine = $request->prezzo_vendita - $totaleCosti;
        
        DB::beginTransaction();
        
        try {
            // Aggiorna l'ordine
            $ordini->update([
                'cliente_id' => $request->cliente_id,
                'traffic_source_id' => $request->traffic_source_id,
                'data' => $request->data,
                'tipo_vendita' => $request->tipo_vendita,
                'link_ordine' => $request->link_ordine,
                'prezzo_vendita' => $request->prezzo_vendita,
                'costo_marketing' => $request->costo_marketing ?? 0,
                'costo_prodotto' => $request->costo_prodotto ?? 0,
                'costo_spedizione' => $request->costo_spedizione ?? 0,
                'altri_costi' => $request->altri_costi ?? 0,
                'margine' => $margine,
                'vat' => $request->vat ?? false,
                'note' => $request->note,
            ]);
            
            // Aggiorna i prodotti associati all'ordine
            $ordini->prodotti()->detach();
            foreach ($request->prodotti as $prodotto) {
                $ordini->prodotti()->attach($prodotto['id'], [
                    'quantita' => $prodotto['quantita'],
                    'prezzo_unitario' => $prodotto['prezzo_unitario'],
                ]);
            }
            
            // Aggiorna gli abbonamenti associati all'ordine
            $ordini->abbonamenti()->detach();
            if ($request->has('abbonamenti')) {
                foreach ($request->abbonamenti as $abbonamento) {
                    $ordini->abbonamenti()->attach($abbonamento['id'], [
                        'data_inizio' => $abbonamento['data_inizio'],
                        'data_fine' => $abbonamento['data_fine'] ?? null,
                        'prezzo' => $abbonamento['prezzo'],
                        'attivo' => true,
                    ]);
                }
            }
            
            // Aggiorna o crea la spedizione
            if ($request->has('spedizione')) {
                if ($ordini->spedizione) {
                    $ordini->spedizione->update([
                        'corriere' => $request->spedizione['corriere'] ?? null,
                        'numero_tracciamento' => $request->spedizione['numero_tracciamento'] ?? null,
                        'indirizzo_spedizione' => $request->spedizione['indirizzo_spedizione'],
                    ]);
                } else {
                    $ordini->spedizione()->create([
                        'corriere' => $request->spedizione['corriere'] ?? null,
                        'numero_tracciamento' => $request->spedizione['numero_tracciamento'] ?? null,
                        'stato' => 'in_preparazione',
                        'indirizzo_spedizione' => $request->spedizione['indirizzo_spedizione'],
                    ]);
                }
            } else if ($ordini->spedizione) {
                // Se non ci sono dati di spedizione ma esiste una spedizione, la eliminiamo
                $ordini->spedizione->delete();
            }
            
            DB::commit();
            
            return redirect()->route('ordini.show', $ordini)
                             ->with('success', 'Ordine aggiornato con successo!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Si è verificato un errore durante l\'aggiornamento dell\'ordine: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ordini $ordini)
    {
        try {
            $ordini->delete();
            return redirect()->route('ordini.index')
                             ->with('success', 'Ordine eliminato con successo!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Si è verificato un errore durante l\'eliminazione dell\'ordine: ' . $e->getMessage()]);
        }
    }
}