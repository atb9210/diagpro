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

        $calcolaPrezzoVendita = function (Get $get, Set $set) {
            \Log::info('=== INIZIO calcolaPrezzoVendita ===', ['timestamp' => now()]);
            
            $prodotti = $get('prodotti') ?? [];
            \Log::info('Prodotti trovati:', ['count' => count($prodotti), 'prodotti' => $prodotti]);
            
            $prezzoVenditaTotale = 0;

            foreach ($prodotti as $index => $prodotto) {
                if (isset($prodotto['prezzo_unitario'], $prodotto['quantita'])) {
                    $subtotale = (float) $prodotto['prezzo_unitario'] * (int) $prodotto['quantita'];
                    $prezzoVenditaTotale += $subtotale;
                    \Log::info("Prodotto {$index}:", [
                        'prezzo_unitario' => $prodotto['prezzo_unitario'],
                        'quantita' => $prodotto['quantita'],
                        'subtotale' => $subtotale
                    ]);
                }
            }

            $abbonamenti = $get('abbonamenti') ?? [];
            \Log::info('Abbonamenti trovati:', ['count' => count($abbonamenti), 'abbonamenti' => $abbonamenti]);
            
            foreach ($abbonamenti as $index => $abbonamento) {
                if (isset($abbonamento['prezzo']) && is_numeric($abbonamento['prezzo'])) {
                    $prezzoVenditaTotale += (float) $abbonamento['prezzo'];
                    \Log::info("Abbonamento {$index}:", ['prezzo' => $abbonamento['prezzo']]);
                }
            }

            $prezzoFormattato = number_format($prezzoVenditaTotale, 2, '.', '');
            \Log::info('=== FINE calcolaPrezzoVendita ===', [
                'prezzo_totale_calcolato' => $prezzoVenditaTotale,
                'prezzo_formattato' => $prezzoFormattato,
                'timestamp' => now()
            ]);
            
            $set('prezzo_vendita', $prezzoFormattato);
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

        $calcolaPrezzoVenditaEMargine = function (Get $get, Set $set) use ($calcolaPrezzoVendita, $updateMargine) {
            $calcolaPrezzoVendita($get, $set);
            $updateMargine($get, $set);
        };

        return $form
            ->schema([
                Forms\Components\Section::make('Informazioni Ordine')
                    ->schema([
                        Forms\Components\Select::make('cliente_id')
                            ->relationship('cliente', 'nome')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\Section::make('Informazioni Generali')
                                    ->schema([
                                        Forms\Components\TextInput::make('nome')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Select::make('tipologia')
                                            ->options([
                                                'privato' => 'Privato',
                                                'azienda' => 'Azienda',
                                            ])
                                            ->default('privato')
                                            ->required()
                                            ->live(),
                                        Forms\Components\TextInput::make('ragione_sociale')
                                             ->label('Ragione Sociale')
                                             ->maxLength(255)
                                             ->visible(fn (Get $get) => $get('tipologia') === 'azienda')
                                             ->required(fn (Get $get) => $get('tipologia') === 'azienda'),
                                     ])->columns(2),
                                 Forms\Components\Section::make('Dati Fiscali')
                                     ->schema([
                                         Forms\Components\TextInput::make('codice_fiscale')
                                             ->label('Codice Fiscale')
                                             ->maxLength(16)
                                             ->visible(fn (Get $get) => $get('tipologia') === 'privato'),
                                         Forms\Components\TextInput::make('partita_iva')
                                             ->label('Partita IVA')
                                             ->maxLength(11)
                                             ->visible(fn (Get $get) => $get('tipologia') === 'azienda'),
                                     ])->columns(2),
                                Forms\Components\Section::make('Contatti')
                                    ->schema([
                                        Forms\Components\Select::make('prefisso_telefonico')
                                            ->label('Prefisso')
                                            ->options([
                                                '+39' => '🇮🇹 +39 (Italia)',
                                                '+33' => '🇫🇷 +33 (Francia)',
                                                '+49' => '🇩🇪 +49 (Germania)',
                                                '+34' => '🇪🇸 +34 (Spagna)',
                                                '+41' => '🇨🇭 +41 (Svizzera)',
                                                '+1' => '🇺🇸 +1 (Stati Uniti)',
                                                '+44' => '🇬🇧 +44 (Regno Unito)',
                                            ])
                                            ->default('+39')
                                            ->searchable()
                                            ->required(),
                                        Forms\Components\TextInput::make('telefono')
                                            ->label('Numero di Cellulare')
                                            ->tel()
                                            ->required()
                                            ->minLength(8)
                                            ->maxLength(15)
                                            ->placeholder('Es. 3331234567'),
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->maxLength(255),
                                    ])->columns(3),
                                Forms\Components\Section::make('Indirizzo di Spedizione')
                                    ->schema([
                                        Forms\Components\TextInput::make('indirizzo_spedizione')
                                             ->label('Indirizzo'),
                                         Forms\Components\TextInput::make('cap_spedizione')
                                             ->label('CAP')
                                             ->maxLength(10),
                                         Forms\Components\TextInput::make('citta_spedizione')
                                             ->label('Città')
                                             ->maxLength(255),
                                         Forms\Components\TextInput::make('provincia_spedizione')
                                             ->label('Provincia')
                                             ->maxLength(5)
                                             ->placeholder('Es. MI, RM, NA'),
                                         Forms\Components\Select::make('stato_spedizione')
                                             ->label('Stato')
                                             ->options([
                                                 'IT' => '🇮🇹 Italia',
                                                 'FR' => '🇫🇷 Francia',
                                                 'DE' => '🇩🇪 Germania',
                                                 'ES' => '🇪🇸 Spagna',
                                                 'CH' => '🇨🇭 Svizzera',
                                             ])
                                             ->default('IT')
                                             ->searchable(),
                                    ])->columns(3),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return \App\Models\Cliente::create($data);
                            }),
                        Forms\Components\Select::make('traffic_source_id')
                            ->relationship('trafficSource', 'nome')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('campagna_id')
                            ->label('Campagna Pubblicitaria')
                            ->relationship('campagna', 'nome_campagna')
                            ->searchable()
                            ->preload()
                            ->placeholder('Seleziona una campagna (opzionale)'),
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
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) use ($calcolaPrezzoVenditaEMargine) {
                                $calcolaPrezzoVenditaEMargine($get, $set);
                            })
                            ->schema([
                                Forms\Components\Select::make('prodotto_id')
                                    ->label('Prodotto')
                                    ->options(\App\Models\Prodotto::where('stato', 'attivo')->pluck('nome', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) use ($calcolaCostoProdotti, $calcolaPrezzoVenditaEMargine) {
                                        \Log::info('Prodotto selezionato:', ['prodotto_id' => $state, 'timestamp' => now()]);
                                        
                                        if ($state) {
                                            $prodotto = \App\Models\Prodotto::find($state);
                                            if ($prodotto) {
                                                \Log::info('Prodotto trovato:', ['nome' => $prodotto->nome, 'prezzo' => $prodotto->prezzo, 'costo' => $prodotto->costo]);
                                                
                                                $set('prezzo_unitario', $prodotto->prezzo);
                                                $set('costo', $prodotto->costo);
                                                
                                                // Debug: verifica stato prima del calcolo
                                                $prezzoVenditaAttuale = $get('../../prezzo_vendita');
                                                \Log::info('Prezzo vendita prima del calcolo:', ['prezzo' => $prezzoVenditaAttuale]);
                                                
                                                // Calcolo immediato
                                                $calcolaCostoProdotti($get, $set);
                                                $calcolaPrezzoVenditaEMargine($get, $set);
                                                
                                                // Delay per forzare l'aggiornamento UI
                                                dispatch(function () use ($get, $set, $calcolaPrezzoVenditaEMargine) {
                                                    \Log::info('Esecuzione delayed trigger');
                                                    $calcolaPrezzoVenditaEMargine($get, $set);
                                                })->afterResponse();
                                            }
                                        }
                                    })
                                    ->createOptionForm([
                                        Forms\Components\Section::make('Informazioni Prodotto')
                                            ->schema([
                                                Forms\Components\TextInput::make('nome')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\Textarea::make('descrizione')
                                                    ->maxLength(65535)
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('costo')
                                                    ->required()
                                                    ->numeric()
                                                    ->prefix('€'),
                                                Forms\Components\TextInput::make('prezzo')
                                                    ->required()
                                                    ->numeric()
                                                    ->prefix('€'),
                                                Forms\Components\Select::make('tipo')
                                                    ->options([
                                                        'fisico' => 'Prodotto Fisico',
                                                        'servizio' => 'Servizio',
                                                    ])
                                                    ->default('fisico')
                                                    ->required()
                                                    ->live(),
                                                Forms\Components\TextInput::make('quantita_disponibile')
                                                    ->label('Quantità Disponibile')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->visible(fn (Forms\Get $get) => $get('tipo') === 'fisico'),
                                                Forms\Components\Select::make('stato')
                                                    ->options([
                                                        'attivo' => 'Attivo',
                                                        'esaurito' => 'Esaurito',
                                                        'discontinuo' => 'Discontinuo',
                                                    ])
                                                    ->default('attivo')
                                                    ->required(),
                                            ])->columns(2),
                                    ])
                                    ->createOptionUsing(function (array $data) {
                                        return \App\Models\Prodotto::create($data);
                                    })
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantita')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) use ($calcolaCostoProdotti, $calcolaPrezzoVenditaEMargine) {
                                        $calcolaCostoProdotti($get, $set);
                                        $calcolaPrezzoVenditaEMargine($get, $set);
                                    }),
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
                                    ->afterStateUpdated(function (Get $get, Set $set) use ($calcolaCostoProdotti, $calcolaPrezzoVenditaEMargine) {
                                        $calcolaCostoProdotti($get, $set);
                                        $calcolaPrezzoVenditaEMargine($get, $set);
                                    }),
                            ])
                            ->columns(6)
                            ->addActionLabel('Aggiungi Prodotto'),
                        Forms\Components\Repeater::make('abbonamenti')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) use ($calcolaPrezzoVenditaEMargine) {
                                $calcolaPrezzoVenditaEMargine($get, $set);
                            })
                            ->schema([
                                Forms\Components\Select::make('abbonamento_id')
                                    ->label('Abbonamento')
                                    ->options(\App\Models\Abbonamento::all()->pluck('nome', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) use ($calcolaCostoProdotti, $calcolaPrezzoVenditaEMargine) {
                                        if ($state) {
                                            $abbonamento = \App\Models\Abbonamento::find($state);
                                            if ($abbonamento) {
                                                $set('prezzo', $abbonamento->prezzo);
                                                $set('costo', $abbonamento->costo);
                                                // Forza il ricalcolo immediato
                                                $calcolaCostoProdotti($get, $set);
                                                $calcolaPrezzoVenditaEMargine($get, $set);
                                            }
                                        }
                                    })
                                    ->createOptionForm([
                                        Forms\Components\Section::make('Informazioni Abbonamento')
                                            ->schema([
                                                Forms\Components\TextInput::make('nome')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\Textarea::make('descrizione')
                                                    ->maxLength(65535)
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('prezzo')
                                                    ->required()
                                                    ->numeric()
                                                    ->prefix('€'),
                                                Forms\Components\TextInput::make('costo')
                                                    ->required()
                                                    ->numeric()
                                                    ->prefix('€'),
                                                Forms\Components\TextInput::make('durata')
                                                    ->required()
                                                    ->numeric()
                                                    ->suffix('giorni'),
                                                Forms\Components\Select::make('frequenza_rinnovo')
                                                    ->options([
                                                        'mensile' => 'Mensile',
                                                        'trimestrale' => 'Trimestrale',
                                                        'semestrale' => 'Semestrale',
                                                        'annuale' => 'Annuale',
                                                    ])
                                                    ->required(),
                                                Forms\Components\Toggle::make('attivo')
                                                    ->default(true)
                                                    ->required(),
                                            ])->columns(2),
                                    ])
                                    ->createOptionUsing(function (array $data) {
                                        return \App\Models\Abbonamento::create($data);
                                    })
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantita')
                                    ->label('Quantità')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->columnSpan(1),
                                Forms\Components\DatePicker::make('data_inizio')
                                    ->required()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('costo')
                                    ->numeric()
                                    ->required()
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated($calcolaCostoProdotti)
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('prezzo')
                                    ->numeric()
                                    ->required()
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) use ($calcolaCostoProdotti, $calcolaPrezzoVenditaEMargine) {
                                        $calcolaCostoProdotti($get, $set);
                                        $calcolaPrezzoVenditaEMargine($get, $set);
                                    })
                                    ->columnSpan(1),
                            ])
                            ->columns(6)
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
                            ->live()
                            ->reactive()
                            ->extraAttributes([
                                'wire:model.live' => 'data.prezzo_vendita',
                                'x-data' => '{}',
                                'x-init' => 'console.log("Prezzo vendita field initialized")'
                            ])
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) use ($updateMargine) {
                                \Log::info('Prezzo vendita aggiornato manualmente:', ['nuovo_prezzo' => $state, 'timestamp' => now()]);
                                $updateMargine($get, $set, $state);
                                
                                // Delay per assicurare l'aggiornamento UI
                                dispatch(function () use ($get, $set, $updateMargine, $state) {
                                    \Log::info('Delayed update margine per prezzo vendita');
                                    $updateMargine($get, $set, $state);
                                })->afterResponse();
                            }),
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
            ->defaultSort('data', 'desc')
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
                Tables\Columns\TextColumn::make('campagna.nome_campagna')
                    ->label('Campagna')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(20)
                    ->placeholder('-')
                    ->tooltip(function (\App\Models\Ordini $record): ?string {
                        return $record->campagna?->nome_campagna;
                    }),
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
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->width('90px')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('costo_prodotto')
                    ->label('Prodotto')
                    ->numeric()
                    ->sortable()
                    ->money('EUR')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->width('90px')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('costo_spedizione')
                    ->label('Spedizione')
                    ->numeric()
                    ->sortable()
                    ->money('EUR')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->width('90px')
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('altri_costi')
                    ->label('Altri')
                    ->numeric()
                    ->sortable()
                    ->money('EUR')
                    ->toggleable(isToggledHiddenByDefault: true)
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
                Tables\Columns\IconColumn::make('costi_dettaglio')
                    ->label('Info Costi')
                    ->icon(fn () => 'heroicon-o-information-circle')
                    ->color('primary')
                    ->tooltip(function (Ordini $record): string {
                        $tooltip = "Dettaglio Costi:\n";
                        $tooltip .= "• Marketing: €" . number_format($record->costo_marketing, 2) . "\n";
                        $tooltip .= "• Prodotto: €" . number_format($record->costo_prodotto, 2) . "\n";
                        $tooltip .= "• Spedizione: €" . number_format($record->costo_spedizione, 2) . "\n";
                        $tooltip .= "• Altri: €" . number_format($record->altri_costi, 2);
                        return $tooltip;
                    })
                    ->alignCenter()
                    ->width('80px'),
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
