<?php

namespace App\Filament\Admin\Resources\RichiestaOrdineResource\Pages;

use App\Filament\Admin\Resources\RichiestaOrdineResource;
use App\Models\RichiestaOrdine;
use App\Models\Ordini;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ViewRichiestaOrdine extends ViewRecord
{
    protected static string $resource = RichiestaOrdineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('prendi_in_carico')
                ->label('Prendi in Carico')
                ->icon('heroicon-o-hand-raised')
                ->color('info')
                ->visible(fn () => $this->record->stato === RichiestaOrdine::STATO_IN_ATTESA)
                ->action(function () {
                    $this->record->update([
                        'stato' => RichiestaOrdine::STATO_IN_VALIDAZIONE,
                        'validato_da' => Auth::id(),
                        'validato_il' => now(),
                    ]);
                    
                    Notification::make()
                        ->title('Richiesta presa in carico')
                        ->success()
                        ->send();
                        
                    $this->refreshFormData([
                        'stato',
                        'validato_da',
                        'validato_il'
                    ]);
                }),
                
            Actions\Action::make('approva')
                ->label('Approva')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->puoEssereApprovata())
                ->form([
                    Forms\Components\Textarea::make('note_validazione')
                        ->label('Note di Approvazione')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'stato' => RichiestaOrdine::STATO_APPROVATO,
                        'note_validazione' => $data['note_validazione'] ?? null,
                        'validato_da' => Auth::id(),
                        'validato_il' => now(),
                    ]);
                    
                    Notification::make()
                        ->title('Richiesta approvata')
                        ->success()
                        ->send();
                        
                    $this->refreshFormData([
                        'stato',
                        'note_validazione',
                        'validato_da',
                        'validato_il'
                    ]);
                }),
                
            Actions\Action::make('rifiuta')
                ->label('Rifiuta')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->puoEssereRifiutata())
                ->form([
                    Forms\Components\Textarea::make('motivo_rifiuto')
                        ->label('Motivo del Rifiuto')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'stato' => RichiestaOrdine::STATO_RIFIUTATO,
                        'motivo_rifiuto' => $data['motivo_rifiuto'],
                        'validato_da' => Auth::id(),
                        'validato_il' => now(),
                    ]);
                    
                    Notification::make()
                        ->title('Richiesta rifiutata')
                        ->success()
                        ->send();
                        
                    $this->refreshFormData([
                        'stato',
                        'motivo_rifiuto',
                        'validato_da',
                        'validato_il'
                    ]);
                }),
                
            Actions\Action::make('converti_ordine')
                ->label('Converti in Ordine')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('primary')
                ->visible(fn () => $this->record->puoEssereConvertita())
                ->requiresConfirmation()
                ->modalHeading('Conferma Conversione')
                ->modalDescription('Sei sicuro di voler convertire questa richiesta in un ordine ufficiale?')
                ->action(function () {
                    DB::transaction(function () {
                        // Crea l'ordine ufficiale
                        $ordine = Ordini::create([
                            'cliente_id' => $this->record->cliente_id,
                            'shop_id' => $this->record->shop_id,
                            'totale' => $this->record->totale,
                            'totale_spedizione' => $this->record->totale_spedizione,
                            'note' => $this->record->note,
                            'status' => 'confermato',
                            'payment_status' => 'in_attesa',
                        ]);
                        
                        // Copia i prodotti
                        foreach ($this->record->prodotti as $prodotto) {
                            $ordine->prodotti()->attach($prodotto->id, [
                                'quantita' => $prodotto->pivot->quantita,
                                'costo' => $prodotto->pivot->prezzo_personalizzato ?? $prodotto->pivot->prezzo_unitario,
                            ]);
                        }
                        
                        // Aggiorna la richiesta
                        $this->record->update([
                            'stato' => RichiestaOrdine::STATO_CONVERTITO,
                            'ordine_id' => $ordine->id,
                        ]);
                    });
                    
                    Notification::make()
                        ->title('Richiesta convertita in ordine')
                        ->success()
                        ->send();
                        
                    $this->refreshFormData([
                        'stato',
                        'ordine_id'
                    ]);
                }),
                
            Actions\Action::make('vai_ordine')
                ->label('Vai all\'Ordine')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->visible(fn () => $this->record->ordine_id)
                ->url(fn () => route('filament.admin.resources.ordinis.view', $this->record->ordine_id))
                ->openUrlInNewTab(),
        ];
    }
}