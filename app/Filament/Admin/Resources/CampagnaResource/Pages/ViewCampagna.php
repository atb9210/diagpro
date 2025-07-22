<?php

namespace App\Filament\Admin\Resources\CampagnaResource\Pages;

use App\Filament\Admin\Resources\CampagnaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Support\Enums\FontWeight;

class ViewCampagna extends ViewRecord
{
    protected static string $resource = CampagnaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Modifica')
                ->icon('heroicon-o-pencil'),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informazioni Campagna')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('nome_campagna')
                                    ->label('Nome Campagna')
                                    ->weight(FontWeight::Bold)
                                    ->size('lg'),
                                    
                                TextEntry::make('data_inizio')
                                    ->label('Data di Inizio')
                                    ->date('d/m/Y'),
                            ]),
                            
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('stato')
                                    ->label('Stato')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'attiva' => 'success',
                                        'pausa' => 'warning',
                                        'terminata' => 'danger',
                                        'in_review' => 'secondary',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => \App\Models\Campagna::STATI[$state] ?? $state),
                                    
                                TextEntry::make('trafficSource.nome')
                                    ->label('Traffic Source')
                                    ->badge()
                                    ->color('primary'),
                                    
                                TextEntry::make('data_fine')
                                    ->label('Data di Fine')
                                    ->formatStateUsing(function ($state) {
                                        return $state ? $state->format('d/m/Y') : 'Indefinita';
                                    }),
                                    
                                TextEntry::make('giorni_rimanenti')
                                    ->label('Giorni Rimanenti')
                                    ->formatStateUsing(function ($record) {
                                        $giorni = $record->giorni_rimanenti;
                                        if ($giorni === null) {
                                            return 'Campagna indefinita';
                                        }
                                        if ($giorni <= 0) {
                                            return 'Campagna terminata';
                                        }
                                        return $giorni . ' giorni';
                                    }),
                            ]),
                    ]),
                    
                Section::make('Budget e Performance')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('spesa')
                                    ->label('Spesa Totale')
                                    ->money('EUR')
                                    ->weight(FontWeight::Bold),
                                    
                                TextEntry::make('budget_type')
                                    ->label('Tipo Budget')
                                    ->formatStateUsing(fn (string $state): string => \App\Models\Campagna::BUDGET_TYPES[$state] ?? $state)
                                    ->badge()
                                    ->color('gray'),
                                    
                                TextEntry::make('costo_conversione')
                                    ->label('Costo per Conversione')
                                    ->money('EUR'),
                            ]),
                            
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('vendite')
                                    ->label('Vendite Totali')
                                    ->getStateUsing(fn ($record): int => $record->ordini()->count())
                                    ->badge()
                                    ->color('success')
                                    ->weight(FontWeight::Bold),
                                    
                                TextEntry::make('costo_per_acquisizione')
                                    ->label('Costo per Acquisizione')
                                    ->getStateUsing(function ($record): string {
                                        $vendite = $record->ordini()->count();
                                        if ($vendite == 0) return '€0,00';
                                        return '€' . number_format($record->spesa / $vendite, 2, ',', '.');
                                    })
                                    ->color('warning'),
                                    
                                TextEntry::make('roi')
                                    ->label('ROI')
                                    ->getStateUsing(function ($record): string {
                                        if ($record->spesa == 0) return '0%';
                                        $ricavi = $record->ordini()->sum('prezzo_vendita');
                                        $roi = (($ricavi - $record->spesa) / $record->spesa) * 100;
                                        return number_format($roi, 1) . '%';
                                    })
                                    ->color(function ($record): string {
                                        if ($record->spesa == 0) return 'gray';
                                        $ricavi = $record->ordini()->sum('prezzo_vendita');
                                        $roi = (($ricavi - $record->spesa) / $record->spesa) * 100;
                                        return $roi > 0 ? 'success' : 'danger';
                                    })
                                    ->weight(FontWeight::Bold),
                            ]),
                            
                        TextEntry::make('margine')
                            ->label('Margine')
                            ->money('EUR'),
                    ]),
                    
                Section::make('Note')
                    ->schema([
                        TextEntry::make('note')
                            ->label('Note')
                            ->placeholder('Nessuna nota')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
    
    public function getTitle(): string
    {
        return 'Dettagli Campagna: ' . $this->record->nome_campagna;
    }
}