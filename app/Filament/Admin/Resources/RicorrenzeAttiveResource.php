<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RicorrenzeAttiveResource\Pages;
use App\Models\RicorrenzeAttive;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RicorrenzeAttiveResource extends Resource
{
    protected static ?string $model = RicorrenzeAttive::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    
    protected static ?string $navigationLabel = 'Ricorrenze Attive';
    
    protected static ?string $modelLabel = 'Ricorrenza Attiva';
    
    protected static ?string $pluralModelLabel = 'Ricorrenze Attive';
    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informazioni Ricorrenza')
                    ->schema([
                        Forms\Components\Select::make('ordini_id')
                            ->relationship('ordine', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "Ordine #{$record->id} - {$record->cliente->nome}")
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('abbonamento_id')
                            ->relationship('abbonamento', 'nome')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('data_inizio')
                            ->required(),
                        Forms\Components\DatePicker::make('data_fine')
                            ->label('Data Scadenza'),
                        Forms\Components\TextInput::make('prezzo')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                        Forms\Components\TextInput::make('costo')
                            ->numeric()
                            ->prefix('€'),
                        Forms\Components\Toggle::make('attivo')
                            ->default(true),
                        Forms\Components\Textarea::make('note')
                            ->label('Note')
                            ->placeholder('Inserisci note, credenziali o altre informazioni...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(RicorrenzeAttive::query()->attive()->with(['ordine.cliente', 'abbonamento']))
            ->defaultSort('data_inizio', 'desc')
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('ordine.id')
                    ->label('Ordine')
                    ->prefix('#')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ordine.cliente.nome')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (RicorrenzeAttive $record): ?string {
                        return $record->ordine?->cliente?->nome;
                    }),
                Tables\Columns\TextColumn::make('abbonamento.nome')
                    ->label('Piano')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->tooltip(function (RicorrenzeAttive $record): ?string {
                        return $record->abbonamento?->nome;
                    }),
                Tables\Columns\TextColumn::make('data_inizio')
                    ->label('Data Inizio')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_fine')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->placeholder('Nessuna scadenza')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('giorni_rimanenti')
                    ->label('Giorni Rimanenti')
                    ->formatStateUsing(function (?int $state): string {
                        if ($state === null) {
                            return 'Illimitato';
                        }
                        if ($state < 0) {
                            return 'Scaduto';
                        }
                        return $state . ' giorni';
                    })
                    ->colors([
                        'success' => fn (?int $state): bool => $state === null || $state > 30,
                        'warning' => fn (?int $state): bool => $state !== null && $state <= 30 && $state > 7,
                        'danger' => fn (?int $state): bool => $state !== null && $state <= 7,
                    ]),
                Tables\Columns\TextColumn::make('prezzo')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('attivo')
                    ->label('Stato')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Attivo' : 'Inattivo')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ]),
                Tables\Columns\IconColumn::make('note')
                    ->label('Info')
                    ->icon(fn ($state) => $state ? 'heroicon-o-information-circle' : null)
                    ->color('primary')
                    ->tooltip(fn ($state) => $state ?: null)
                    ->alignCenter()
                    ->width('60px'),
                Tables\Columns\TextColumn::make('ordine.data')
                    ->label('Data Ordine')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('abbonamento_id')
                    ->label('Piano')
                    ->relationship('abbonamento', 'nome')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('in_scadenza')
                    ->label('In Scadenza (30 giorni)')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('data_fine')
                              ->where('data_fine', '<=', now()->addDays(30))
                              ->where('data_fine', '>=', now())
                    ),
                Tables\Filters\Filter::make('scaduti')
                    ->label('Scaduti')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('data_fine')
                              ->where('data_fine', '<', now())
                    ),
                Tables\Filters\Filter::make('illimitati')
                    ->label('Senza Scadenza')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNull('data_fine')
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (RicorrenzeAttive $record): string => 
                        route('filament.admin.resources.ordinis.view', $record->ordini_id)
                    ),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRicorrenzeAttives::route('/'),
            'edit' => Pages\EditRicorrenzeAttive::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->attive();
    }
}
