<?php

namespace App\Filament\Admin\Resources\ShopResource\RelationManagers;

use App\Models\Prodotto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdottiRelationManager extends RelationManager
{
    protected static string $relationship = 'prodotti';

    protected static ?string $title = 'Prodotti del Shop';
    
    protected static ?string $modelLabel = 'Prodotto';
    
    protected static ?string $pluralModelLabel = 'Prodotti';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('prezzo_personalizzato')
                    ->label('Prezzo Personalizzato')
                    ->helperText('Lascia vuoto per usare il prezzo base del prodotto')
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01),
                    
                Forms\Components\Toggle::make('attivo')
                    ->label('Attivo nel Shop')
                    ->default(true)
                    ->helperText('Se disattivato, il prodotto non sarà visibile nel shop'),
                    
                Forms\Components\TextInput::make('ordine')
                    ->label('Ordine di Visualizzazione')
                    ->numeric()
                    ->default(0)
                    ->helperText('Numero più basso = mostrato prima'),
                    
                Forms\Components\KeyValue::make('configurazione')
                    ->label('Configurazione Personalizzata')
                    ->helperText('Configurazioni specifiche per questo shop (es. descrizione personalizzata)')
                    ->keyLabel('Chiave')
                    ->valueLabel('Valore')
                    ->addActionLabel('Aggiungi configurazione')
                    ->reorderable(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nome')
            ->columns([
                Tables\Columns\ImageColumn::make('immagine_copertina')
                    ->label('Immagine')
                    ->circular()
                    ->size(40),
                    
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome Prodotto')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('prezzo')
                    ->label('Prezzo Base')
                    ->money('EUR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('prezzo_personalizzato')
                    ->label('Prezzo Shop')
                    ->money('EUR')
                    ->getStateUsing(function ($record) {
                        return $record->pivot->prezzo_personalizzato ?? $record->prezzo;
                    })
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('attivo')
                    ->label('Attivo')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->pivot->attivo)
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('ordine')
                    ->label('Ordine')
                    ->getStateUsing(fn ($record) => $record->pivot->ordine)
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('stato')
                    ->label('Stato Prodotto')
                    ->colors([
                        'success' => 'attivo',
                        'warning' => 'esaurito',
                        'danger' => 'discontinuo',
                    ])
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('attivo')
                    ->label('Stato nel Shop')
                    ->options([
                        '1' => 'Attivo',
                        '0' => 'Disattivato',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value'])) {
                            return $query->wherePivot('attivo', $data['value']);
                        }
                        return $query;
                    }),
                    
                Tables\Filters\SelectFilter::make('stato')
                    ->label('Stato Prodotto')
                    ->options([
                        'attivo' => 'Attivo',
                        'esaurito' => 'Esaurito',
                        'discontinuo' => 'Discontinuo',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Aggiungi Prodotto')
                    ->modalHeading('Collega Prodotto')
                    ->modalDescription('Seleziona il prodotto da collegare e configura le impostazioni per questo shop.')
                    ->recordSelectSearchColumns(['nome', 'sku', 'descrizione'])
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        $shopId = $this->getOwnerRecord()->id;
                        return $query->where('stato', '!=', 'discontinuo')
                            ->whereDoesntHave('shops', function (Builder $subQuery) use ($shopId) {
                                $subQuery->where('shop_id', $shopId);
                            })
                            ->orderBy('nome');
                    })
                    ->recordTitle(fn ($record) => $record->nome . ' (SKU: ' . $record->sku . ') - €' . number_format($record->prezzo, 2))
                    ->form(function (Form $form): Form {
                        return $form->schema([
                            Forms\Components\Select::make('recordId')
                                ->label('Seleziona Prodotto')
                                ->placeholder('Scegli un prodotto da collegare...')
                                ->searchable()
                                ->required()
                                ->options(function () {
                                    $shopId = $this->getOwnerRecord()->id;
                                    return Prodotto::where('stato', '!=', 'discontinuo')
                                        ->whereDoesntHave('shops', function (Builder $subQuery) use ($shopId) {
                                            $subQuery->where('shop_id', $shopId);
                                        })
                                        ->orderBy('nome')
                                        ->get()
                                        ->mapWithKeys(function ($prodotto) {
                                            return [$prodotto->id => $prodotto->nome . ' (SKU: ' . $prodotto->sku . ') - €' . number_format($prodotto->prezzo, 2)];
                                        })
                                        ->toArray();
                                })
                                ->getSearchResultsUsing(function (string $search) {
                                    $shopId = $this->getOwnerRecord()->id;
                                    return Prodotto::where('stato', '!=', 'discontinuo')
                                        ->whereDoesntHave('shops', function (Builder $subQuery) use ($shopId) {
                                            $subQuery->where('shop_id', $shopId);
                                        })
                                        ->where(function ($query) use ($search) {
                                            $query->where('nome', 'like', "%{$search}%")
                                                ->orWhere('sku', 'like', "%{$search}%")
                                                ->orWhere('descrizione', 'like', "%{$search}%");
                                        })
                                        ->orderBy('nome')
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(function ($prodotto) {
                                            return [$prodotto->id => $prodotto->nome . ' (SKU: ' . $prodotto->sku . ') - €' . number_format($prodotto->prezzo, 2)];
                                        })
                                        ->toArray();
                                }),
                                
                            Forms\Components\TextInput::make('prezzo_personalizzato')
                                ->label('Prezzo Personalizzato')
                                ->helperText('Lascia vuoto per usare il prezzo base del prodotto')
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01),
                                
                            Forms\Components\Toggle::make('attivo')
                                ->label('Attivo nel Shop')
                                ->default(true)
                                ->helperText('Se disattivato, il prodotto non sarà visibile nel shop'),
                                
                            Forms\Components\TextInput::make('ordine')
                                ->label('Ordine di Visualizzazione')
                                ->numeric()
                                ->default(0)
                                ->helperText('Numero più basso = mostrato prima'),
                                
                            Forms\Components\KeyValue::make('configurazione')
                                ->label('Configurazione Personalizzata')
                                ->helperText('Configurazioni specifiche per questo shop (es. descrizione personalizzata)')
                                ->keyLabel('Chiave')
                                ->valueLabel('Valore')
                                ->addActionLabel('Aggiungi configurazione')
                                ->reorderable(false),
                        ]);
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        // Converte l'array configurazione in JSON per il database
                        if (isset($data['configurazione']) && is_array($data['configurazione'])) {
                            $data['configurazione'] = json_encode($data['configurazione']);
                        }
                        return $data;
                    })
                    ->modalWidth('2xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Modifica')
                    ->form(fn (Form $form): Form => $this->form($form))
                    ->mutateFormDataUsing(function (array $data): array {
                        // Converte l'array configurazione in JSON per il database
                        if (isset($data['configurazione']) && is_array($data['configurazione'])) {
                            $data['configurazione'] = json_encode($data['configurazione']);
                        }
                        return $data;
                    })
                    ->mutateRecordDataUsing(function (array $data): array {
                        // Converte il JSON configurazione in array per il form
                        if (isset($data['configurazione']) && is_string($data['configurazione'])) {
                            $data['configurazione'] = json_decode($data['configurazione'], true) ?? [];
                        }
                        return $data;
                    }),
                    
                Tables\Actions\DetachAction::make()
                    ->label('Rimuovi dal Shop'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Rimuovi dal Shop'),
                        
                    Tables\Actions\BulkAction::make('attiva')
                        ->label('Attiva nel Shop')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->pivot->update(['attivo' => true]);
                            }
                        }),
                        
                    Tables\Actions\BulkAction::make('disattiva')
                        ->label('Disattiva nel Shop')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->pivot->update(['attivo' => false]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('ordine', 'asc');
    }
}