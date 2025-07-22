<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RichiestaOrdineResource\Pages;
use App\Models\RichiestaOrdine;
use App\Models\Ordini;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RichiestaOrdineResource extends Resource
{
    protected static ?string $model = RichiestaOrdine::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationLabel = 'Richieste Ordine';
    
    protected static ?string $modelLabel = 'Richiesta Ordine';
    
    protected static ?string $pluralModelLabel = 'Richieste Ordine';
    
    protected static ?string $navigationGroup = 'Gestione Ordini';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Validazione')
                    ->schema([
                        Forms\Components\Select::make('stato')
                            ->label('Stato')
                            ->options(RichiestaOrdine::getStati())
                            ->required()
                            ->reactive(),
                            
                        Forms\Components\Textarea::make('note_validazione')
                            ->label('Note di Validazione')
                            ->rows(3)
                            ->visible(fn ($get) => in_array($get('stato'), [
                                RichiestaOrdine::STATO_IN_VALIDAZIONE,
                                RichiestaOrdine::STATO_APPROVATO
                            ])),
                            
                        Forms\Components\Textarea::make('motivo_rifiuto')
                            ->label('Motivo Rifiuto')
                            ->rows(3)
                            ->required()
                            ->visible(fn ($get) => $get('stato') === RichiestaOrdine::STATO_RIFIUTATO),
                    ])
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_richiesta')
                    ->label('Numero Richiesta')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('shop.nome')
                    ->label('Mini Shop')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('dati_cliente')
                    ->label('Cliente')
                    ->formatStateUsing(fn ($state) => $state['nome'] ?? 'N/A')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('totale')
                    ->label('Totale')
                    ->money('EUR')
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('stato')
                    ->label('Stato')
                    ->formatStateUsing(fn ($state) => RichiestaOrdine::getStati()[$state] ?? $state)
                    ->colors([
                        'warning' => RichiestaOrdine::STATO_IN_ATTESA,
                        'info' => RichiestaOrdine::STATO_IN_VALIDAZIONE,
                        'success' => RichiestaOrdine::STATO_APPROVATO,
                        'danger' => RichiestaOrdine::STATO_RIFIUTATO,
                        'primary' => RichiestaOrdine::STATO_CONVERTITO,
                    ]),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Richiesta')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('validato_il')
                    ->label('Validato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('validatoDa.name')
                    ->label('Validato da')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stato')
                    ->label('Stato')
                    ->options(RichiestaOrdine::getStati()),
                    
                Tables\Filters\SelectFilter::make('shop_id')
                    ->label('Mini Shop')
                    ->relationship('shop', 'nome'),
                    
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Al'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\Action::make('prendi_in_carico')
                    ->label('Prendi in Carico')
                    ->icon('heroicon-o-hand-raised')
                    ->color('info')
                    ->visible(fn (RichiestaOrdine $record) => $record->stato === RichiestaOrdine::STATO_IN_ATTESA)
                    ->action(function (RichiestaOrdine $record) {
                        $record->update([
                            'stato' => RichiestaOrdine::STATO_IN_VALIDAZIONE,
                            'validato_da' => Auth::id(),
                            'validato_il' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Richiesta presa in carico')
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\Action::make('approva')
                    ->label('Approva')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (RichiestaOrdine $record) => $record->puoEssereApprovata())
                    ->form([
                        Forms\Components\Textarea::make('note_validazione')
                            ->label('Note di Approvazione')
                            ->rows(3),
                    ])
                    ->action(function (RichiestaOrdine $record, array $data) {
                        $record->update([
                            'stato' => RichiestaOrdine::STATO_APPROVATO,
                            'note_validazione' => $data['note_validazione'] ?? null,
                            'validato_da' => Auth::id(),
                            'validato_il' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Richiesta approvata')
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\Action::make('rifiuta')
                    ->label('Rifiuta')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (RichiestaOrdine $record) => $record->puoEssereRifiutata())
                    ->form([
                        Forms\Components\Textarea::make('motivo_rifiuto')
                            ->label('Motivo del Rifiuto')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (RichiestaOrdine $record, array $data) {
                        $record->update([
                            'stato' => RichiestaOrdine::STATO_RIFIUTATO,
                            'motivo_rifiuto' => $data['motivo_rifiuto'],
                            'validato_da' => Auth::id(),
                            'validato_il' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Richiesta rifiutata')
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\Action::make('converti_ordine')
                    ->label('Converti in Ordine')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('primary')
                    ->visible(fn (RichiestaOrdine $record) => $record->puoEssereConvertita())
                    ->requiresConfirmation()
                    ->modalHeading('Conferma Conversione')
                    ->modalDescription('Sei sicuro di voler convertire questa richiesta in un ordine ufficiale?')
                    ->action(function (RichiestaOrdine $record) {
                        DB::transaction(function () use ($record) {
                            // Crea l'ordine ufficiale
                            $ordine = Ordini::create([
                                'cliente_id' => $record->cliente_id,
                                'shop_id' => $record->shop_id,
                                'totale' => $record->totale,
                                'totale_spedizione' => $record->totale_spedizione,
                                'note' => $record->note,
                                'status' => 'confermato',
                                'payment_status' => 'in_attesa',
                                'dati_spedizione' => $record->dati_spedizione,
                            ]);
                            
                            // Copia i prodotti
                            foreach ($record->prodotti as $prodotto) {
                                $ordine->prodotti()->attach($prodotto->id, [
                                    'quantita' => $prodotto->pivot->quantita,
                                    'costo' => $prodotto->pivot->prezzo_personalizzato ?? $prodotto->pivot->prezzo_unitario,
                                ]);
                            }
                            
                            // Aggiorna la richiesta
                            $record->update([
                                'stato' => RichiestaOrdine::STATO_CONVERTITO,
                                'ordine_id' => $ordine->id,
                            ]);
                        });
                        
                        Notification::make()
                            ->title('Richiesta convertita in ordine')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->can('delete_any_richiesta_ordine')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informazioni Richiesta')
                    ->schema([
                        TextEntry::make('numero_richiesta')
                            ->label('Numero Richiesta'),
                        TextEntry::make('shop.nome')
                            ->label('Mini Shop'),
                        TextEntry::make('stato_label')
                            ->label('Stato')
                            ->badge()
                            ->color(fn (RichiestaOrdine $record) => $record->stato_color),
                        TextEntry::make('created_at')
                            ->label('Data Richiesta')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),
                    
                Section::make('Dati Cliente')
                    ->schema([
                        TextEntry::make('dati_cliente.nome')
                            ->label('Nome'),
                        TextEntry::make('dati_cliente.email')
                            ->label('Email'),
                        TextEntry::make('dati_cliente.telefono')
                            ->label('Telefono'),
                    ])
                    ->columns(2),
                    
                Section::make('Dati Spedizione')
                    ->schema([
                        TextEntry::make('dati_spedizione.indirizzo')
                            ->label('Indirizzo'),
                        TextEntry::make('dati_spedizione.citta')
                            ->label('Città'),
                        TextEntry::make('dati_spedizione.cap')
                            ->label('CAP'),
                        TextEntry::make('dati_spedizione.provincia')
                            ->label('Provincia'),
                    ])
                    ->columns(2),
                    
                Section::make('Prodotti')
                    ->schema([
                        RepeatableEntry::make('prodotti')
                            ->schema([
                                TextEntry::make('pivot.nome_prodotto')
                                    ->label('Prodotto'),
                                TextEntry::make('pivot.sku')
                                    ->label('SKU'),
                                TextEntry::make('pivot.quantita')
                                    ->label('Quantità'),
                                TextEntry::make('pivot.prezzo_unitario')
                                    ->label('Prezzo Unitario')
                                    ->money('EUR'),
                                TextEntry::make('pivot.subtotale')
                                    ->label('Subtotale')
                                    ->money('EUR'),
                            ])
                            ->columns(5)
                    ]),
                    
                Section::make('Totali')
                    ->schema([
                        TextEntry::make('totale_spedizione')
                            ->label('Spedizione')
                            ->money('EUR'),
                        TextEntry::make('totale')
                            ->label('Totale')
                            ->money('EUR'),
                    ])
                    ->columns(2),
                    
                Section::make('Validazione')
                    ->schema([
                        TextEntry::make('validatoDa.name')
                            ->label('Validato da'),
                        TextEntry::make('validato_il')
                            ->label('Validato il')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('note_validazione')
                            ->label('Note Validazione'),
                        TextEntry::make('motivo_rifiuto')
                            ->label('Motivo Rifiuto')
                            ->visible(fn (RichiestaOrdine $record) => $record->stato === RichiestaOrdine::STATO_RIFIUTATO),
                    ])
                    ->columns(2)
                    ->visible(fn (RichiestaOrdine $record) => $record->validato_da),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRichiesteOrdine::route('/'),
            'view' => Pages\ViewRichiestaOrdine::route('/{record}'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::inAttesa()->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::inAttesa()->count();
        return $count > 0 ? 'warning' : null;
    }
}