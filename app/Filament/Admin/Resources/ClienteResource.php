<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ClienteResource\Pages;
use App\Filament\Admin\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Tapp\FilamentGoogleAutocomplete\Forms\Components\GoogleAutocomplete;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Clienti';
    
    protected static ?string $modelLabel = 'Cliente';
    
    protected static ?string $pluralModelLabel = 'Clienti';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                        Forms\Components\Select::make('traffic_source_id')
                            ->relationship('trafficSource', 'nome')
                            ->searchable()
                            ->preload()
                            ->label('Sorgente di Traffico'),
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
                                '+43' => '🇦🇹 +43 (Austria)',
                                '+1' => '🇺🇸 +1 (Stati Uniti)',
                                '+44' => '🇬🇧 +44 (Regno Unito)',
                                '+31' => '🇳🇱 +31 (Paesi Bassi)',
                                '+32' => '🇧🇪 +32 (Belgio)',
                                '+351' => '🇵🇹 +351 (Portogallo)',
                                '+30' => '🇬🇷 +30 (Grecia)',
                                '+48' => '🇵🇱 +48 (Polonia)',
                                '+420' => '🇨🇿 +420 (Repubblica Ceca)',
                                '+36' => '🇭🇺 +36 (Ungheria)',
                                '+385' => '🇭🇷 +385 (Croazia)',
                                '+386' => '🇸🇮 +386 (Slovenia)',
                                '+421' => '🇸🇰 +421 (Slovacchia)',
                                '+40' => '🇷🇴 +40 (Romania)',
                                '+359' => '🇧🇬 +359 (Bulgaria)',
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
                            ->placeholder('Es. 3331234567')
                            ->helperText('Inserisci solo numeri di cellulare (8-15 cifre)')
                            ->rules([
                                'regex:/^[0-9]{8,15}$/',
                            ])
                            ->validationMessages([
                                'regex' => 'Il numero di telefono deve contenere solo cifre (8-15 caratteri).',
                                'min_length' => 'Il numero di telefono deve essere di almeno 8 cifre.',
                                'max_length' => 'Il numero di telefono non può superare le 15 cifre.',
                            ]),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Indirizzo di Spedizione')
                        ->schema([
                            GoogleAutocomplete::make('google_search_spedizione')
                                ->label('Cerca Indirizzo')
                                ->autocompletePlaceholder('Inizia a digitare l\'indirizzo...')
                                ->language('it')
                                ->withFields([
                                    Forms\Components\TextInput::make('indirizzo_spedizione')
                                        ->label('Indirizzo')
                                        ->required()
                                        ->extraInputAttributes([
                                            'data-google-field' => '{street_number} {route}',
                                        ]),
                                    Forms\Components\TextInput::make('cap_spedizione')
                                        ->label('CAP')
                                        ->required()
                                        ->maxLength(10)
                                        ->extraInputAttributes([
                                            'data-google-field' => 'postal_code',
                                        ]),
                                    Forms\Components\TextInput::make('citta_spedizione')
                                        ->label('Città')
                                        ->required()
                                        ->maxLength(255)
                                        ->extraInputAttributes([
                                            'data-google-field' => 'locality',
                                        ]),
                                    Forms\Components\TextInput::make('provincia_spedizione')
                                        ->label('Provincia')
                                        ->maxLength(5)
                                        ->placeholder('Es. MI, RM, NA')
                                        ->extraInputAttributes([
                                            'data-google-field' => 'administrative_area_level_2',
                                        ]),
                                    Forms\Components\Select::make('stato_spedizione')
                                        ->label('Stato')
                                        ->options([
                                            'IT' => '🇮🇹 Italia',
                                            'FR' => '🇫🇷 Francia',
                                            'DE' => '🇩🇪 Germania',
                                            'ES' => '🇪🇸 Spagna',
                                            'CH' => '🇨🇭 Svizzera',
                                            'AT' => '🇦🇹 Austria',
                                            'US' => '🇺🇸 Stati Uniti',
                                            'GB' => '🇬🇧 Regno Unito',
                                        ])
                                        ->default('IT')
                                        ->required()
                                        ->searchable()
                                        ->extraInputAttributes([
                                            'data-google-field' => 'country',
                                        ]),
                                ]),
                    ])->columns(3),

                Forms\Components\Section::make('Indirizzo di Fatturazione')
                    ->schema([
                        Forms\Components\Toggle::make('fatturazione_uguale_spedizione')
                            ->label('Indirizzo di fatturazione uguale a quello di spedizione')
                            ->default(true)
                            ->live()
                            ->columnSpanFull(),
                        GoogleAutocomplete::make('google_search_fatturazione')
                            ->label('Cerca Indirizzo')
                            ->autocompletePlaceholder('Inizia a digitare l\'indirizzo...')
                            ->language('it')
                            ->visible(fn (Get $get) => !$get('fatturazione_uguale_spedizione'))
                            ->withFields([
                                Forms\Components\TextInput::make('indirizzo_fatturazione')
                                    ->label('Indirizzo')
                                    ->visible(fn (Get $get) => !$get('fatturazione_uguale_spedizione'))
                                    ->required(fn (Get $get) => !$get('fatturazione_uguale_spedizione'))
                                    ->extraInputAttributes([
                                        'data-google-field' => '{street_number} {route}',
                                    ]),
                                Forms\Components\TextInput::make('cap_fatturazione')
                                    ->label('CAP')
                                    ->visible(fn (Get $get) => !$get('fatturazione_uguale_spedizione'))
                                    ->required(fn (Get $get) => !$get('fatturazione_uguale_spedizione'))
                                    ->maxLength(10)
                                    ->extraInputAttributes([
                                        'data-google-field' => 'postal_code',
                                    ]),
                                Forms\Components\TextInput::make('citta_fatturazione')
                                    ->label('Città')
                                    ->visible(fn (Get $get) => !$get('fatturazione_uguale_spedizione'))
                                    ->required(fn (Get $get) => !$get('fatturazione_uguale_spedizione'))
                                    ->maxLength(255)
                                    ->extraInputAttributes([
                                        'data-google-field' => 'locality',
                                    ]),
                                Forms\Components\TextInput::make('provincia_fatturazione')
                                    ->label('Provincia')
                                    ->visible(fn (Get $get) => !$get('fatturazione_uguale_spedizione'))
                                    ->maxLength(5)
                                    ->placeholder('Es. MI, RM, NA')
                                    ->extraInputAttributes([
                                        'data-google-field' => 'administrative_area_level_2',
                                    ]),
                                Forms\Components\Select::make('stato_fatturazione')
                                    ->label('Stato')
                                    ->options([
                                        'IT' => '🇮🇹 Italia',
                                        'FR' => '🇫🇷 Francia',
                                        'DE' => '🇩🇪 Germania',
                                        'ES' => '🇪🇸 Spagna',
                                        'CH' => '🇨🇭 Svizzera',
                                        'AT' => '🇦🇹 Austria',
                                        'US' => '🇺🇸 Stati Uniti',
                                        'GB' => '🇬🇧 Regno Unito',
                                    ])
                                    ->visible(fn (Get $get) => !$get('fatturazione_uguale_spedizione'))
                                    ->required(fn (Get $get) => !$get('fatturazione_uguale_spedizione'))
                                    ->searchable()
                                    ->extraInputAttributes([
                                        'data-google-field' => 'country',
                                    ]),
                            ]),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => 
                $query->withSum('ordini', 'prezzo_vendita')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('telefono')
                    ->label('Telefono')
                    ->formatStateUsing(function ($record) {
                        if (!$record->telefono) return '-';
                        
                        $bandiere = [
                            '+39' => '🇮🇹',
                            '+33' => '🇫🇷',
                            '+49' => '🇩🇪',
                            '+34' => '🇪🇸',
                            '+41' => '🇨🇭',
                            '+43' => '🇦🇹',
                            '+1' => '🇺🇸',
                            '+44' => '🇬🇧',
                            '+31' => '🇳🇱',
                            '+32' => '🇧🇪',
                            '+351' => '🇵🇹',
                            '+30' => '🇬🇷',
                            '+48' => '🇵🇱',
                            '+420' => '🇨🇿',
                            '+36' => '🇭🇺',
                            '+385' => '🇭🇷',
                            '+386' => '🇸🇮',
                            '+421' => '🇸🇰',
                            '+40' => '🇷🇴',
                            '+359' => '🇧🇬',
                        ];
                        
                        $prefisso = $record->prefisso_telefonico ?? '+39';
                        $bandiera = $bandiere[$prefisso] ?? '🌍';
                        
                        return $bandiera . ' ' . $prefisso . ' ' . $record->telefono;
                    })
                    ->copyable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('whatsapp_link')
                    ->label('WhatsApp')
                    ->icon(fn ($record) => $record->telefono ? 'heroicon-o-chat-bubble-left-right' : 'heroicon-o-phone-x-mark')
                    ->color(fn ($record) => $record->telefono ? 'success' : 'gray')
                    ->tooltip(fn ($record) => $record->telefono ? 'Apri chat WhatsApp' : 'Numero di telefono non disponibile')
                    ->url(function ($record) {
                        if ($record->telefono) {
                            $prefisso = $record->prefisso_telefonico ?? '+39';
                            $numeroCompleto = str_replace('+', '', $prefisso) . $record->telefono;
                            $messaggio = urlencode('Ciao ' . $record->nome . ', ti contatto da DiagPro per...');
                            return 'https://wa.me/' . $numeroCompleto . '?text=' . $messaggio;
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->alignCenter()
                    ->width('60px')
                    ->state(fn ($record) => $record->telefono ? 'available' : 'unavailable'),
                Tables\Columns\TextColumn::make('ordini_sum_prezzo_vendita')
                    ->label('Totale Speso')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label('Totale Generale'),
                    ])
                    ->color(fn ($state) => $state > 1000 ? 'success' : ($state > 500 ? 'warning' : 'gray')),
                Tables\Columns\IconColumn::make('ha_abbonamenti_attivi')
                    ->label('Abbonamento Attivo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('abbonamenti_attivi_formattati')
                    ->label('Info Abbonamenti')
                    ->icon('heroicon-o-information-circle')
                    ->tooltip(fn ($record) => $record ? ($record->abbonamenti_attivi_formattati ?: 'Nessun abbonamento') : 'Nessun abbonamento')
                    ->color(function ($state, $record) {
                        if (!$record || !$record->abbonamenti_attivi_formattati || $record->abbonamenti_attivi_formattati === 'Nessun abbonamento') return 'gray';
                        
                        // Controlla se ci sono abbonamenti in scadenza nei prossimi 7 giorni
                        $abbonamenti = $record->abbonamenti_attivi;
                        foreach ($abbonamenti as $abbonamento) {
                            if ($abbonamento->pivot->data_fine) {
                                $days = now()->diffInDays($abbonamento->pivot->data_fine, false);
                                if ($days < 0) return 'danger'; // Almeno uno scaduto
                                if ($days <= 7) return 'warning'; // Almeno uno in scadenza
                            }
                        }
                        return 'success';
                    })
                    ->visible(fn ($record) => $record && $record->abbonamenti_attivi_formattati && $record->abbonamenti_attivi_formattati !== 'Nessun abbonamento'),
                Tables\Columns\TextColumn::make('trafficSource.nome')
                    ->label('Fonte di Traffico')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('trafficSource.iconaUrl')
                    ->label('Icona Fonte')
                    ->circular()
                    ->size(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrato il')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('ordini_sum_prezzo_vendita', 'desc')
            ->filters([
                Tables\Filters\Filter::make('ha_abbonamenti_attivi')
                    ->label('Con Abbonamento Attivo')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('ordini.abbonamenti', function ($q) {
                            $q->wherePivot('attivo', true)
                              ->where(function($subQ) {
                                  $subQ->where('ordini_abbonamento.data_fine', '>=', now())
                                       ->orWhereNull('ordini_abbonamento.data_fine');
                              });
                        })
                    ),
                    
                Tables\Filters\Filter::make('abbonamenti_in_scadenza')
                    ->label('Abbonamenti in Scadenza (7 giorni)')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('ordini.abbonamenti', function ($q) {
                            $q->wherePivot('attivo', true)
                              ->wherePivot('data_fine', '>=', now())
                              ->wherePivot('data_fine', '<=', now()->addDays(7));
                        })
                    ),
                    
                Tables\Filters\Filter::make('totale_speso')
                    ->form([
                        Forms\Components\TextInput::make('min_speso')
                            ->label('Importo minimo')
                            ->numeric()
                            ->prefix('€'),
                        Forms\Components\TextInput::make('max_speso')
                            ->label('Importo massimo')
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_speso'],
                                fn (Builder $query, $amount): Builder => 
                                    $query->whereHas('ordini', function ($q) use ($amount) {
                                        $q->havingRaw('SUM(prezzo_vendita) >= ?', [$amount]);
                                    })
                            )
                            ->when(
                                $data['max_speso'],
                                fn (Builder $query, $amount): Builder => 
                                    $query->whereHas('ordini', function ($q) use ($amount) {
                                        $q->havingRaw('SUM(prezzo_vendita) <= ?', [$amount]);
                                    })
                            );
                    }),
                    
                Tables\Filters\SelectFilter::make('traffic_source_id')
                    ->label('Fonte di Traffico')
                    ->relationship('trafficSource', 'nome')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_orders')
                    ->label('Vedi Ordini')
                    ->icon('heroicon-o-shopping-cart')
                    ->url(fn (Cliente $record): string => 
                        route('filament.admin.resources.ordinis.index', [
                            'tableFilters' => ['cliente_id' => ['value' => $record->id]]
                        ])
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export_clienti')
                        ->label('Esporta Clienti Selezionati')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            // Implementazione esportazione CSV
                            $csv = "Nome,Email,Telefono,Totale Speso,Abbonamento Attivo,Abbonamenti Attivi\n";
                            foreach ($records as $cliente) {
                                $csv .= sprintf(
                                    "\"%s\",\"%s\",\"%s\",%.2f,%s,\"%s\"\n",
                                    $cliente->nome,
                                    $cliente->email ?? '',
                                    $cliente->telefono ?? '',
                                    $cliente->ordini_sum_prezzo_vendita ?? 0,
                                    $cliente->ha_abbonamenti_attivi ? 'Sì' : 'No',
                                    $cliente->abbonamenti_attivi_formattati
                                );
                            }
                            
                            return response()->streamDownload(
                                fn () => print($csv),
                                'clienti_' . now()->format('Y-m-d_H-i-s') . '.csv',
                                ['Content-Type' => 'text/csv']
                            );
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
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
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
