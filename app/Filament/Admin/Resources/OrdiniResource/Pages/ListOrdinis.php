<?php

namespace App\Filament\Admin\Resources\OrdiniResource\Pages;

use App\Filament\Admin\Resources\OrdiniResource;
use App\Models\Ordini;
use App\Models\Impostazione;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Colors\Color;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ListOrdinis extends ListRecords
{
    protected static string $resource = OrdiniResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        // Usa il mese corrente come periodo di default
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        // Query base per il periodo selezionato
        $ordiniQuery = Ordini::whereBetween('data', [$startDate, $endDate]);
        
        // Calcoli statistici
        $totaleOrdini = $ordiniQuery->count();
        $totaleVenduto = $ordiniQuery->sum('prezzo_vendita');
        
        // Calcolo del profitto (margine totale)
        $totaleProfitto = $ordiniQuery->get()->sum(function ($ordine) {
            return $ordine->prezzo_vendita - ($ordine->costo_marketing + $ordine->costo_prodotto + $ordine->costo_spedizione + $ordine->altri_costi);
        });
        
        // Calcolo del margine percentuale
        $marginePercentuale = $totaleVenduto > 0 ? ($totaleProfitto / $totaleVenduto) * 100 : 0;
        
        // Obiettivo configurabile dalle impostazioni
        $obiettivo = Impostazione::get('obiettivo_profitto_mensile', 2000);
        
        // Percentuale obiettivo
        $percentualeObiettivo = $obiettivo > 0 ? ($totaleProfitto / $obiettivo) * 100 : 0;
        
        $stats = [
            'totale_ordini' => $totaleOrdini,
            'totale_venduto' => $totaleVenduto,
            'totale_profitto' => $totaleProfitto,
            'margine_percentuale' => $marginePercentuale,
            'obiettivo' => $obiettivo,
            'percentuale_obiettivo' => $percentualeObiettivo,
            'periodo' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
        ];
        
        return view('filament.admin.resources.ordini-resource.pages.stats-header', compact('stats'));
    }
}
