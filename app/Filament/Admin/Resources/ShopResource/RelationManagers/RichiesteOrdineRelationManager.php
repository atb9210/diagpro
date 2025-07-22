<?php

namespace App\Filament\Admin\Resources\ShopResource\RelationManagers;

use App\Models\RichiestaOrdine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class RichiesteOrdineRelationManager extends RelationManager
{
    protected static string $relationship = 'richiesteOrdine';
    
    protected static ?string $title = 'Richieste Ordine';
    
    protected static ?string $modelLabel = 'Richiesta Ordine';
    
    protected static ?string $pluralModelLabel = 'Richieste Ordine';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Validazione')
                    ->schema([
                        Forms\Components\Select::make('stato')
                            ->options([
                                RichiestaOrdine::STATO_IN_ATTESA => 'In Attesa Validazione',
                                RichiestaOrdine::STATO_IN_VALIDAZIONE => 'In Validazione',
                                RichiestaOrdine::STATO_APPROVATO => 'Approvato',
                                RichiestaOrdine::STATO_RIFIUTATO => 'Rifiutato',
                                RichiestaOrdine::STATO_CONVERTITO => 'Convertito',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('note_validazione')
                            ->label('Note di Validazione')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('motivo_rifiuto')
                            ->label('Motivo Rifiuto')
                            ->rows(3)
                            ->visible(fn (Forms\Get $get) => $get('stato') === RichiestaOrdine::STATO_RIFIUTATO)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_richiesta')
            ->columns([
                Tables\Columns\TextColumn::make('numero_richiesta')
                    ->label('N. Richiesta')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable(),
                Tables\Columns\TextColumn::make('dati_cliente')
                        ->label('Cliente')
                        ->formatStateUsing(fn ($state) => $state['nome'] ?? 'N/A')
                        ->searchable()
                        ->sortable(),
                Tables\Columns\TextColumn::make('totale')
                    ->label('Totale')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        RichiestaOrdine::STATO_IN_ATTESA => 'warning',
                        RichiestaOrdine::STATO_IN_VALIDAZIONE => 'info',
                        RichiestaOrdine::STATO_APPROVATO => 'success',
                        RichiestaOrdine::STATO_RIFIUTATO => 'danger',
                        RichiestaOrdine::STATO_CONVERTITO => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        RichiestaOrdine::STATO_IN_ATTESA => 'In Attesa',
                        RichiestaOrdine::STATO_IN_VALIDAZIONE => 'In Validazione',
                        RichiestaOrdine::STATO_APPROVATO => 'Approvato',
                        RichiestaOrdine::STATO_RIFIUTATO => 'Rifiutato',
                        RichiestaOrdine::STATO_CONVERTITO => 'Convertito',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Richiesta')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('validatoDa.name')
                    ->label('Validato da')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('validato_il')
                    ->label('Data Validazione')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stato')
                    ->options([
                        RichiestaOrdine::STATO_IN_ATTESA => 'In Attesa Validazione',
                        RichiestaOrdine::STATO_IN_VALIDAZIONE => 'In Validazione',
                        RichiestaOrdine::STATO_APPROVATO => 'Approvato',
                        RichiestaOrdine::STATO_RIFIUTATO => 'Rifiutato',
                        RichiestaOrdine::STATO_CONVERTITO => 'Convertito',
                    ])
                    ->native(false),
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
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Dal: ' . \Carbon\Carbon::parse($data['created_from'])->format('d/m/Y');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Al: ' . \Carbon\Carbon::parse($data['created_until'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->headerActions([
                // Non permettiamo la creazione manuale di richieste ordine
                // Queste vengono create solo dai Mini Shop
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->url(fn (RichiestaOrdine $record): string => route('filament.admin.resources.richiesta-ordines.view', $record))
                        ->openUrlInNewTab(),
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
                    Tables\Actions\EditAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Azioni bulk per gestire multiple richieste
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}