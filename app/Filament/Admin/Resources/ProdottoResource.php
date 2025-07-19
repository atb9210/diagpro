<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProdottoResource\Pages;
use App\Filament\Admin\Resources\ProdottoResource\RelationManagers;
use App\Models\Prodotto;
use App\Models\Categoria;
use App\Models\Fornitore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdottoResource extends Resource
{
    protected static ?string $model = Prodotto::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    
    protected static ?string $navigationLabel = 'Prodotti';
    
    protected static ?string $modelLabel = 'Prodotto';
    
    protected static ?string $pluralModelLabel = 'Prodotti';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                            ->required(),
                        Forms\Components\TextInput::make('quantita_disponibile')
                            ->numeric()
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
                
                Forms\Components\Section::make('Dettagli Aggiuntivi')
                    ->schema([
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Codice prodotto univoco'),
                        Forms\Components\Select::make('categoria_id')
                            ->label('Categoria')
                            ->relationship('categoria', 'nome')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nome')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('descrizione'),
                                Forms\Components\ColorPicker::make('colore')
                                    ->required()
                                    ->default('#3B82F6'),
                                Forms\Components\Toggle::make('attiva')
                                    ->required()
                                    ->default(true),
                            ])
                            ->default(function () {
                                return Categoria::where('nome', 'Uncategorized')->first()?->id;
                            }),
                        Forms\Components\DatePicker::make('data_arrivo')
                            ->label('Data di Arrivo')
                            ->placeholder('Seleziona data di arrivo')
                            ->helperText('Data prevista di arrivo del prodotto (opzionale)'),
                        Forms\Components\Select::make('fornitore_id')
                            ->label('Fornitore')
                            ->relationship('fornitore', 'nome')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nome')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('descrizione'),
                                Forms\Components\FileUpload::make('logo')
                                    ->image()
                                    ->directory('fornitori/loghi'),
                                Forms\Components\TextInput::make('link_sito')
                                    ->url()
                                    ->label('Sito Web'),
                                Forms\Components\TextInput::make('email')
                                    ->email(),
                                Forms\Components\TextInput::make('telefono')
                                    ->tel(),
                                Forms\Components\Textarea::make('indirizzo'),
                                Forms\Components\Toggle::make('attivo')
                                    ->required()
                                    ->default(true),
                            ]),
                    ])->columns(2),
                
                Forms\Components\Section::make('Caratteristiche Fisiche')
                    ->schema([
                        Forms\Components\TextInput::make('peso')
                            ->label('Peso (kg)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('kg'),
                        Forms\Components\TextInput::make('lunghezza')
                            ->label('Lunghezza (cm)')
                            ->numeric()
                            ->step(0.1)
                            ->suffix('cm'),
                        Forms\Components\TextInput::make('larghezza')
                            ->label('Larghezza (cm)')
                            ->numeric()
                            ->step(0.1)
                            ->suffix('cm'),
                        Forms\Components\TextInput::make('altezza')
                            ->label('Altezza (cm)')
                            ->numeric()
                            ->step(0.1)
                            ->suffix('cm'),
                    ])
                    ->columns(2)
                    ->visible(fn (Forms\Get $get) => $get('tipo') === 'fisico'),
                
                Forms\Components\Section::make('Immagini')
                    ->schema([
                        Forms\Components\FileUpload::make('immagini')
                            ->label('Immagini Prodotto')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->imageEditor()
                            ->directory('prodotti/immagini')
                            ->visibility('public')
                            ->maxFiles(10)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('immagine_copertina')
                            ->label('Immagine Copertina')
                            ->image()
                            ->imageEditor()
                            ->directory('prodotti/copertine')
                            ->visibility('public')
                            ->helperText('Immagine principale del prodotto')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fisico' => 'success',
                        'servizio' => 'info',
                    }),
                Tables\Columns\TextColumn::make('costo')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('prezzo')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantita_disponibile')
                    ->label('Qtà')
                    ->numeric()
                    ->sortable()
                    ->placeholder('N/A')
                    ->width('80px')
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('stato')
                    ->icon(fn (string $state): string => match ($state) {
                        'attivo' => 'heroicon-o-check-circle',
                        'esaurito' => 'heroicon-o-exclamation-triangle',
                        'discontinuo' => 'heroicon-o-x-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'attivo' => 'success',
                        'esaurito' => 'warning',
                        'discontinuo' => 'danger',
                    })
                    ->tooltip(fn (string $state): string => match ($state) {
                        'attivo' => 'Prodotto Attivo',
                        'esaurito' => 'Prodotto Esaurito',
                        'discontinuo' => 'Prodotto Discontinuo',
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stato')
                    ->options([
                        'attivo' => 'Attivo',
                        'esaurito' => 'Esaurito',
                        'discontinuo' => 'Discontinuo',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'fisico' => 'Prodotto Fisico',
                        'servizio' => 'Servizio',
                    ])
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('cambiaStato')
                        ->label('Cambia Stato')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('nuovo_stato')
                                ->label('Nuovo Stato')
                                ->options([
                                    'attivo' => 'Attivo',
                                    'esaurito' => 'Esaurito',
                                    'discontinuo' => 'Discontinuo',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            foreach ($records as $record) {
                                $record->update(['stato' => $data['nuovo_stato']]);
                            }
                        })
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
            'index' => Pages\ListProdottos::route('/'),
            'create' => Pages\CreateProdotto::route('/create'),
            'edit' => Pages\EditProdotto::route('/{record}/edit'),
        ];
    }
}
