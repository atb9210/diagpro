<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CampagnaResource\Pages;
use App\Filament\Admin\Resources\CampagnaResource\Widgets;
use App\Models\Campagna;
use App\Models\TrafficSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;

class CampagnaResource extends Resource
{
    protected static ?string $model = Campagna::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    
    protected static ?string $navigationLabel = 'Pubblicità';
    
    protected static ?string $modelLabel = 'Campagna';
    
    protected static ?string $pluralModelLabel = 'Campagne';
    
    protected static ?string $navigationGroup = 'Marketing';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informazioni Campagna')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nome_campagna')
                                    ->label('Nome Campagna')
                                    ->required()
                                    ->maxLength(255),
                                    
                                Forms\Components\DatePicker::make('data_inizio')
                                    ->label('Data di Inizio')
                                    ->required()
                                    ->default(now()),
                            ]),
                            
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('stato')
                                    ->label('Stato')
                                    ->options(Campagna::STATI)
                                    ->required()
                                    ->default('attiva'),
                                    
                                Forms\Components\Select::make('traffic_source_id')
                                    ->label('Traffic Source')
                                    ->relationship('trafficSource', 'nome')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                    
                                Forms\Components\DatePicker::make('data_fine')
                                    ->label('Data di Fine')
                                    ->nullable()
                                    ->after('data_inizio')
                                    ->helperText('Lascia vuoto per campagna indefinita'),
                            ]),
                    ]),
                    
                Section::make('Budget e Costi')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('budget')
                                    ->label('Budget')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Importo del budget allocato'),
                                    
                                Forms\Components\TextInput::make('spesa')
                                    ->label('Spesa')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Importo speso finora'),
                                    
                                Forms\Components\Select::make('budget_type')
                                    ->label('Tipo Budget')
                                    ->options(Campagna::BUDGET_TYPES)
                                    ->required()
                                    ->default('mensile'),

                            ]),

                    ]),
                    
                Section::make('Note')
                    ->schema([
                        Forms\Components\Textarea::make('note')
                            ->label('Note')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome_campagna')
                    ->label('Nome Campagna')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                    
                TextColumn::make('data_inizio')
                    ->label('Data Inizio')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                BadgeColumn::make('stato')
                    ->label('Stato')
                    ->colors([
                        'success' => 'attiva',
                        'warning' => 'pausa',
                        'danger' => 'terminata',
                        'secondary' => 'in_review',
                    ])
                    ->formatStateUsing(fn (string $state): string => Campagna::STATI[$state] ?? $state),
                    
                TextColumn::make('trafficSource.nome')
                    ->label('Traffic Source')
                    ->badge()
                    ->color('primary'),
                    
                TextColumn::make('spesa')
                    ->label('Spesa')
                    ->money('EUR')
                    ->sortable(),
                    
                TextColumn::make('data_fine')
                    ->label('Data Fine')
                    ->date('d/m/Y')
                    ->placeholder('Indefinita')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('giorni_rimanenti')
                    ->label('Scadenza')
                    ->getStateUsing(function ($record) {
                        $giorni = $record->giorni_rimanenti;
                        if ($giorni === null) {
                            return '∞';
                        }
                        return $giorni;
                    })
                    ->badge()
                    ->color(function ($state) {
                        if ($state === '∞') {
                            return 'info';
                        }
                        $giorni = (int) $state;
                        if ($giorni <= 0) {
                            return 'danger';
                        } elseif ($giorni <= 7) {
                            return 'warning';
                        } elseif ($giorni <= 30) {
                            return 'success';
                        }
                        return 'primary';
                    })
                    ->sortable(),
                    
                TextColumn::make('budget')
                    ->label('Budget')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('budget_type')
                    ->label('Tipo Budget')
                    ->formatStateUsing(fn (string $state): string => Campagna::BUDGET_TYPES[$state] ?? $state)
                    ->badge()
                    ->color('gray'),
                    
                TextColumn::make('vendite')
                    ->label('Vendite')
                    ->getStateUsing(fn (Campagna $record): int => $record->ordini()->count())
                    ->badge()
                    ->color('success'),
                    
                TextColumn::make('totale_vendite')
                    ->label('Tot. Vendite')
                    ->getStateUsing(function ($record) {
                        return '€ ' . number_format($record->ordini()->sum('prezzo_vendita'), 2, ',', '.');
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('totale_profit')
                    ->label('Profit')
                    ->getStateUsing(function ($record) {
                        return '€ ' . number_format($record->totale_profit, 2, ',', '.');
                    })
                    ->color(function ($state) {
                        $value = (float) str_replace(['€ ', '.', ','], ['', '', '.'], $state);
                        return $value >= 0 ? 'success' : 'danger';
                    })
                    ->sortable(),
                    
                TextColumn::make('totale_costi')
                    ->label('Tot. Costi')
                    ->getStateUsing(function ($record) {
                        return '€ ' . number_format($record->totale_costi, 2, ',', '.');
                    })
                    ->color('warning')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('cpa_totale')
                    ->label('CPA Totale')
                    ->getStateUsing(function ($record) {
                        return '€ ' . number_format($record->cpa_totale, 2, ',', '.');
                    })
                    ->color('info')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('costo_per_acquisizione')
                    ->label('CPA')
                    ->getStateUsing(function (Campagna $record): string {
                        $vendite = $record->ordini()->count();
                        if ($vendite == 0) return '€0,00';
                        return '€' . number_format($record->spesa / $vendite, 2, ',', '.');
                    })
                    ->color('warning'),
                    
                TextColumn::make('roi')
                    ->label('ROI')
                    ->getStateUsing(function (Campagna $record): string {
                        if ($record->spesa == 0) return '0%';
                        $ricavi = $record->ordini()->sum('prezzo_vendita');
                        $roi = (($ricavi - $record->spesa) / $record->spesa) * 100;
                        return number_format($roi, 1) . '%';
                    })
                    ->color(function (Campagna $record): string {
                        if ($record->spesa == 0) return 'gray';
                        $ricavi = $record->ordini()->sum('prezzo_vendita');
                        $roi = (($ricavi - $record->spesa) / $record->spesa) * 100;
                        return $roi > 0 ? 'success' : 'danger';
                    }),
                    
                TextColumn::make('created_at')
                    ->label('Creata il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('stato')
                    ->label('Stato')
                    ->options(Campagna::STATI),
                    
                SelectFilter::make('traffic_source_id')
                    ->label('Traffic Source')
                    ->relationship('trafficSource', 'nome'),
                    
                SelectFilter::make('budget_type')
                    ->label('Tipo Budget')
                    ->options(Campagna::BUDGET_TYPES),
                    
                TrashedFilter::make()
                    ->label('Archiviate'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\Action::make('quickEdit')
                        ->label('Quick Edit')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->form([
                            DatePicker::make('data_fine')
                                ->label('Data Fine')
                                ->native(false),
                            TextInput::make('spesa')
                                ->label('Spesa')
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01)
                                ->helperText('Importo speso per questa campagna'),
                            TextInput::make('budget')
                                ->label('Budget')
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01)
                                ->helperText('Budget allocato per questa campagna'),
                            Select::make('stato')
                                ->label('Stato')
                                ->options(Campagna::STATI)
                                ->required(),
                        ])
                        ->fillForm(fn (Campagna $record): array => [
                            'data_fine' => $record->data_fine,
                            'spesa' => $record->spesa,
                            'budget' => $record->budget,
                            'stato' => $record->stato,
                        ])
                        ->action(function (array $data, Campagna $record): void {
                            $record->update($data);
                        })
                        ->successNotificationTitle('Campagna aggiornata con successo')
                        ->modalHeading('Modifica Rapida Campagna')
                        ->modalSubmitActionLabel('Salva Modifiche'),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->label('Archivia')
                        ->modalHeading('Archivia Campagna')
                        ->modalDescription('Sei sicuro di voler archiviare questa campagna? Potrà essere ripristinata in seguito.')
                        ->modalSubmitActionLabel('Archivia')
                        ->successNotificationTitle('Campagna archiviata con successo'),
                    RestoreAction::make()
                        ->label('Ripristina')
                        ->modalHeading('Ripristina Campagna')
                        ->modalDescription('Sei sicuro di voler ripristinare questa campagna?')
                        ->modalSubmitActionLabel('Ripristina')
                        ->successNotificationTitle('Campagna ripristinata con successo'),
                    ForceDeleteAction::make()
                        ->label('Elimina definitivamente')
                        ->modalHeading('Elimina Definitivamente')
                        ->modalDescription('ATTENZIONE: Questa azione eliminerà definitivamente la campagna e tutti i dati associati. Questa operazione non può essere annullata!')
                        ->modalSubmitActionLabel('Elimina definitivamente')
                        ->color('danger')
                        ->successNotificationTitle('Campagna eliminata definitivamente'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Archivia selezionate')
                        ->modalHeading('Archivia Campagne')
                        ->modalDescription('Sei sicuro di voler archiviare le campagne selezionate? Potranno essere ripristinate in seguito.')
                        ->modalSubmitActionLabel('Archivia')
                        ->successNotificationTitle('Campagne archiviate con successo'),
                    RestoreBulkAction::make()
                        ->label('Ripristina selezionate')
                        ->modalHeading('Ripristina Campagne')
                        ->modalDescription('Sei sicuro di voler ripristinare le campagne selezionate?')
                        ->modalSubmitActionLabel('Ripristina')
                        ->successNotificationTitle('Campagne ripristinate con successo'),
                    ForceDeleteBulkAction::make()
                        ->label('Elimina definitivamente selezionate')
                        ->modalHeading('Elimina Definitivamente')
                        ->modalDescription('ATTENZIONE: Questa azione eliminerà definitivamente le campagne selezionate e tutti i dati associati. Questa operazione non può essere annullata!')
                        ->modalSubmitActionLabel('Elimina definitivamente')
                        ->color('danger')
                        ->successNotificationTitle('Campagne eliminate definitivamente'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListCampagnas::route('/'),
            'create' => Pages\CreateCampagna::route('/create'),
            'view' => Pages\ViewCampagna::route('/{record}'),
            'edit' => Pages\EditCampagna::route('/{record}/edit'),
        ];
    }
    
    public static function getWidgets(): array
    {
        return [
            Widgets\CampagnaStatsWidget::class,
        ];
    }
}