<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrdiniResource\Pages;
use App\Filament\Admin\Resources\OrdiniResource\RelationManagers\SpedizioneRelationManager;
use App\Models\Ordini;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Filament\Admin\Resources\OrdiniResource\RelationManagers;
use Filament\Tables;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Model;

class OrdiniResource extends Resource
{
    protected static ?string $model = Ordini::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    protected static ?string $navigationLabel = 'Ordini';
    
    protected static ?string $modelLabel = 'Ordine';
    
    protected static ?string $pluralModelLabel = 'Ordini';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $calcolaCostoProdotti = function (Get $get, Set $set) {
            $prodotti = $get('prodotti') ?? [];
            $costoProdottiTotale = 0;

            foreach ($prodotti as $prodotto) {
                if (isset($prodotto['costo'], $prodotto['quantita'])) {
                    $costoProdottiTotale += (float) $prodotto['costo'] * (int) $prodotto['quantita'];
                }
            }

            $abbonamenti = $get('abbonamenti') ?? [];
            foreach ($abbonamenti as $abbonamento) {
                if (isset($abbonamento['costo']) && is_numeric($abbonamento['costo'])) {
                    $costoProdottiTotale += (float) $abbonamento['costo'];
                }
            }

            $set('costo_prodotto', number_format($costoProdottiTotale, 2, '.', ''));
        };

        $updateMargine = function (Get $get, Set $set) {
            $prezzoVendita = (float) $get('prezzo_vendita');
            $costoMarketing = (float) $get('costo_marketing');
            $costoProdotto = (float) $get('costo_prodotto');
            $costoSpedizione = (float) $get('costo_spedizione');
            $altriCosti = (float) $get('altri_costi');
            $margine = $prezzoVendita - ($costoMarketing + $costoProdotto + $costoSpedizione + $altriCosti);
            $set('margine', number_format($margine, 2, '.', ''));
        };

        return $form
            ->schema([
                Forms\Components\Section::make('Informazioni Ordine')
                    ->schema([
                        Forms\Components\Select::make('cliente_id')
                            ->relationship('cliente', 'nome')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('traffic_source_id')
                            ->relationship('trafficSource', 'nome')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('data')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('tipo_vendita')
                            ->options([
                                'online' => 'Online',
                                'appuntamento' => 'Appuntamento',
                                'contrassegno' => 'Contrassegno',
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status Ordine')
                            ->options([
                                'da_processare' => 'Da Processare',
                                'imballato' => 'Imballato',
                                'spedito' => 'Spedito',
                                'rimborsato' => 'Rimborsato',
                                'consegnato' => 'Consegnato',
                                'reso' => 'Reso',
                                'sostituzione' => 'Sostituzione',
                            ])
                            ->default('da_processare')
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->label('Status Pagamento')
                            ->options([
                                'pending' => 'In Attesa',
                                'processing' => 'In Elaborazione',
                                'paid' => 'Pagato',
                                'failed' => 'Fallito',
                                'cancelled' => 'Annullato',
                                'refunded' => 'Rimborsato',
                                'partially_refunded' => 'Parzialmente Rimborsato',
                                'chargeback' => 'Chargeback',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\TextInput::make('link_ordine')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('vat')
                            ->label('IVA applicata')
                            ->inline(false),
                        Forms\Components\Textarea::make('note')
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Prodotti e Abbonamenti')
                    ->schema([
                        Forms\Components\Repeater::make('prodotti')
                            ->schema([
                                Forms\Components\Select::make('prodotto_id')
                                    ->label('Prodotto')
                                    ->options(\App\Models\Prodotto::all()->pluck('nome', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) use ($calcolaCostoProdotti) {
                                        $prodotto = \App\Models\Prodotto::find($state);
                                        if ($prodotto) {
                                            $set('prezzo_unitario', $prodotto->prezzo);
                                            $set('costo', $prodotto->costo);
                                        }
                                        $calcolaCostoProdotti($get, $set);
                                    })
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantita')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated($calcolaCostoProdotti),
                                Forms\Components\TextInput::make('costo')
                                    ->numeric()
                                    ->required()
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated($calcolaCostoProdotti),
                                Forms\Components\TextInput::make('prezzo_unitario')
                                    ->numeric()
                                    ->required()
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated($calcolaCostoProdotti),
                            ])
                            ->columns(6)
                            ->addActionLabel('Aggiungi Prodotto'),
                        Forms\Components\Repeater::make('abbonamenti')
                            ->schema([
                                Forms\Components\Select::make('abbonamento_id')
                                    ->label('Abbonamento')
                                    ->options(\App\Models\Abbonamento::all()->pluck('nome', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) use ($calcolaCostoProdotti) {
                                        $abbonamento = \App\Models\Abbonamento::find($state);
                                        if ($abbonamento) {
                                            $set('prezzo', $abbonamento->prezzo);
                                            $set('costo', $abbonamento->costo);
                                            $startDate = $get('data_inizio');
                                            if ($startDate) {
                                                $set('data_scadenza', date('Y-m-d', strtotime($startDate . ' + ' . $abbonamento->durata . ' days')));
                                            }
                                        }
                                        $calcolaCostoProdotti($get, $set);
                                    })
                                    ->columnSpan(2),
                                Forms\Components\DatePicker::make('data_inizio')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                        $abbonamentoId = $get('abbonamento_id');
                                        if ($abbonamentoId && $state) {
                                            $abbonamento = \App\Models\Abbonamento::find($abbonamentoId);
                                            if ($abbonamento) {
                                                $set('data_scadenza', date('Y-m-d', strtotime($state . ' + ' . $abbonamento->durata . ' days')));
                                            }
                                        }
                                    }),
                                Forms\Components\TextInput::make('data_scadenza')
                                    ->label('Data Scadenza')
                                    ->disabled(),
                                Forms\Components\TextInput::make('costo')
                                    ->numeric()
                                    ->required()
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated($calcolaCostoProdotti),
                                Forms\Components\TextInput::make('prezzo')
                                    ->numeric()
                                    ->required()
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated($calcolaCostoProdotti),
                            ])
                            ->columns(5)
                            ->addActionLabel('Aggiungi Abbonamento'),

                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Placeholder::make('total_products')
                                    ->label('Totale Costo Prodotti')
                                    ->content(function (Get $get) {
                                        $prodotti = $get('prodotti') ?? [];
                                        $total = 0;
                                        foreach ($prodotti as $prodotto) {
                                            if (isset($prodotto['costo'], $prodotto['quantita'])) {
                                                $total += (float) $prodotto['costo'] * (int) $prodotto['quantita'];
                                            }
                                        }
                                        return '€' . number_format($total, 2, '.', '');
                                    }),
                                Forms\Components\Placeholder::make('total_subscriptions')
                                    ->label('Totale Costo Abbonamenti')
                                    ->content(function (Get $get) {
                                        $abbonamenti = $get('abbonamenti') ?? [];
                                        $total = 0;
                                        foreach ($abbonamenti as $abbonamento) {
                                            if (isset($abbonamento['costo']) && is_numeric($abbonamento['costo'])) {
                                                $total += (float) $abbonamento['costo'];
                                            }
                                        }
                                        return '€' . number_format($total, 2, '.', '');
                                    }),
                            ])->columns(2),
                    ]),



                Forms\Components\Section::make('Costi e Margine')
                    ->schema([
                        Forms\Components\TextInput::make('prezzo_vendita')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->live(onBlur: true)
                            ->afterStateUpdated($updateMargine),
                        Forms\Components\TextInput::make('costo_marketing')
                            ->numeric()
                            ->prefix('€')
                            ->live(onBlur: true)
                            ->default(0.00)
                            ->afterStateUpdated($updateMargine),
                        Forms\Components\TextInput::make('costo_prodotto')
                            ->numeric()
                            ->prefix('€')
                            ->live(onBlur: true)
                            ->default(0.00)
                            ->afterStateUpdated($updateMargine),
                        Forms\Components\TextInput::make('costo_spedizione')
                            ->numeric()
                            ->prefix('€')
                            ->live(onBlur: true)
                            ->default(0.00)
                            ->afterStateUpdated($updateMargine),
                        Forms\Components\TextInput::make('altri_costi')
                            ->numeric()
                            ->prefix('€')
                            ->live(onBlur: true)
                            ->default(0.00)
                            ->afterStateUpdated($updateMargine),
                        Forms\Components\TextInput::make('margine')
                            ->numeric()
                            ->prefix('€')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->deferLoading()
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuovo Ordine'),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('data')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable()
                    ->width('100px'),
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->limit(20)
                    ->tooltip(function (\App\Models\Ordini $record): ?string {
                        return $record->cliente?->nome;
                    }),
                Tables\Columns\ImageColumn::make('trafficSource.icona')
                    ->label('Sorgente')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(url('/images/default-icon.svg'))
                    ->toggleable()
                    ->size(32),
                Tables\Columns\BadgeColumn::make('tipo_vendita')
                    ->colors([
                        'info' => 'appuntamento',
                        'success' => 'online',
                        'warning' => 'contrassegno',
                    ])
                    ->toggleable()
                    ->size('sm'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'da_processare' => 'Da Processare',
                        'imballato' => 'Imballato',
                        'spedito' => 'Spedito',
                        'rimborsato' => 'Rimborsato',
                        'consegnato' => 'Consegnato',
                        'reso' => 'Reso',
                        'sostituzione' => 'Sostituzione',
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'da_processare',
                        'warning' => 'imballato',
                        'success' => ['spedito', 'consegnato'],
                        'danger' => ['rimborsato', 'reso'],
                        'info' => 'sostituzione',
                    ])
                    ->toggleable()
                    ->size('sm'),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Pagamento')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'In Attesa',
                        'processing' => 'In Elaborazione',
                        'paid' => 'Pagato',
                        'failed' => 'Fallito',
                        'cancelled' => 'Annullato',
                        'refunded' => 'Rimborsato',
                        'partially_refunded' => 'Parz. Rimborsato',
                        'chargeback' => 'Chargeback',
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'processing',
                        'success' => 'paid',
                        'danger' => ['failed', 'cancelled', 'chargeback'],
                        'info' => ['refunded', 'partially_refunded'],
                    ])
                    ->toggleable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('link_ordine')
                    ->label('Link')
                    ->icon('heroicon-m-link')
                    ->url(fn (Ordini $record): ?string => $record->link_ordine)
                    ->openUrlInNewTab()
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => $state ? 'VAI' : '-')
                    ->width('60px')
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('prezzo_vendita')
                    ->label('Vendita')
                    ->numeric()
                    ->sortable()
                    ->money('EUR')
                    ->toggleable()
                    ->width('90px')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('costo_marketing')
                    ->label('Marketing')
                    ->numeric()
                    ->sortable()
                    ->money('EUR')
                    ->toggleable()
                    ->width('90px')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('costo_prodotto')
                    ->label('Prodotto')
                    ->numeric()
                    ->sortable()
                    ->money('EUR')
                    ->toggleable()
                    ->width('90px')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('costo_spedizione')
                    ->label('Spedizione')
                    ->numeric()
                    ->sortable()
                    ->money('EUR')
                    ->toggleable()
                    ->width('90px')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('altri_costi')
                    ->label('Altri')
                    ->numeric()
                    ->sortable()
                    ->money('EUR')
                    ->toggleable()
                    ->width('80px')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('costi_totali')
                    ->label('Tot. Costi')
                    ->numeric()
                    ->state(function (Ordini $record): float {
                        return $record->costo_marketing + $record->costo_prodotto + $record->costo_spedizione + $record->altri_costi;
                    })
                    ->sortable()
                    ->money('EUR')
                    ->toggleable()
                    ->width('90px')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('margine')
                    ->numeric()
                    ->state(function (Ordini $record): float {
                        return $record->prezzo_vendita - ($record->costo_marketing + $record->costo_prodotto + $record->costo_spedizione + $record->altri_costi);
                    })
                    ->sortable()
                    ->money('EUR')
                    ->color('success')
                    ->toggleable()
                    ->width('90px')
                    ->alignment('right'),
                Tables\Columns\IconColumn::make('vat')
                    ->boolean()
                    ->toggleable()
                    ->width('60px')
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato')
                    ->dateTime('d/m H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->width('100px'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aggiornato')
                    ->dateTime('d/m H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->width('100px'),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Data da'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Data fino'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'Da: ' . \Carbon\Carbon::parse($data['created_from'])->format('d/m/Y');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Fino: ' . \Carbon\Carbon::parse($data['created_until'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'da_processare' => 'Da Processare',
                        'imballato' => 'Imballato',
                        'spedito' => 'Spedito',
                        'rimborsato' => 'Rimborsato',
                        'consegnato' => 'Consegnato',
                        'reso' => 'Reso',
                        'sostituzione' => 'Sostituzione',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Pagamento')
                    ->options([
                        'pending' => 'In Attesa',
                        'processing' => 'In Elaborazione',
                        'paid' => 'Pagato',
                        'failed' => 'Fallito',
                        'cancelled' => 'Annullato',
                        'refunded' => 'Rimborsato',
                        'partially_refunded' => 'Parzialmente Rimborsato',
                        'chargeback' => 'Chargeback',
                    ])
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SpedizioneRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdinis::route('/'),
            'create' => Pages\CreateOrdini::route('/create'),
            'view' => Pages\ViewOrdini::route('/{record}'),
            'edit' => Pages\EditOrdini::route('/{record}/edit'),
        ];
    }
}
