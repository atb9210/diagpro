<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SpedizioneResource\Pages;
use App\Filament\Admin\Resources\SpedizioneResource\RelationManagers;
use App\Models\Spedizione;
use App\Models\Ordini;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpedizioneResource extends Resource
{
    protected static ?string $model = Spedizione::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    
    protected static ?string $navigationLabel = 'Spedizioni';
    
    protected static ?string $modelLabel = 'Spedizione';
    
    protected static ?string $pluralModelLabel = 'Spedizioni';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('ordini_id')
                    ->label('Ordine')
                    ->relationship('ordine', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Ordini $record): string => "#{$record->id} - {$record->cliente->nome} {$record->cliente->cognome}")
                    ->searchable(['id'])
                    ->preload()
                    ->required(),
                    
                Forms\Components\TextInput::make('corriere')
                    ->label('Corriere')
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('numero_tracciamento')
                    ->label('Numero Tracciamento')
                    ->maxLength(255),
                    
                Forms\Components\Select::make('stato')
                    ->label('Stato')
                    ->options([
                        'in_preparazione' => 'In Preparazione',
                        'spedito' => 'Spedito',
                        'consegnato' => 'Consegnato',
                        'annullato' => 'Annullato',
                    ])
                    ->default('in_preparazione')
                    ->required(),
                    
                Forms\Components\DatePicker::make('data_spedizione')
                    ->label('Data Spedizione'),
                    
                Forms\Components\DatePicker::make('data_consegna_prevista')
                    ->label('Data Consegna Prevista'),
                    
                Forms\Components\DatePicker::make('data_consegna_effettiva')
                    ->label('Data Consegna Effettiva'),
                    
                Forms\Components\Textarea::make('indirizzo_spedizione')
                    ->label('Indirizzo Spedizione')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                    
                Forms\Components\Textarea::make('note')
                    ->label('Note')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ordini_id')
                    ->label('ID Ordine')
                    ->numeric()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('ordine.cliente.nome')
                    ->label('Cliente')
                    ->formatStateUsing(fn (Spedizione $record): string => 
                        $record->ordine->cliente->nome . ' ' . $record->ordine->cliente->cognome
                    )
                    ->searchable(['ordine.cliente.nome', 'ordine.cliente.cognome']),
                    
                Tables\Columns\TextColumn::make('corriere')
                    ->label('Corriere')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('numero_tracciamento')
                    ->label('Tracciamento')
                    ->searchable()
                    ->copyable(),
                    
                Tables\Columns\BadgeColumn::make('stato')
                    ->label('Stato')
                    ->colors([
                        'warning' => 'in_preparazione',
                        'primary' => 'spedito',
                        'success' => 'consegnato',
                        'danger' => 'annullato',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_preparazione' => 'In Preparazione',
                        'spedito' => 'Spedito',
                        'consegnato' => 'Consegnato',
                        'annullato' => 'Annullato',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('data_spedizione')
                    ->label('Data Spedizione')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('data_consegna_prevista')
                    ->label('Consegna Prevista')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('data_consegna_effettiva')
                    ->label('Consegna Effettiva')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aggiornato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stato')
                    ->label('Stato')
                    ->options([
                        'in_preparazione' => 'In Preparazione',
                        'spedito' => 'Spedito',
                        'consegnato' => 'Consegnato',
                        'annullato' => 'Annullato',
                    ]),
                    
                Tables\Filters\Filter::make('data_spedizione')
                    ->form([
                        Forms\Components\DatePicker::make('spedito_da')
                            ->label('Spedito da'),
                        Forms\Components\DatePicker::make('spedito_fino')
                            ->label('Spedito fino'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['spedito_da'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_spedizione', '>=', $date),
                            )
                            ->when(
                                $data['spedito_fino'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_spedizione', '<=', $date),
                            );
                    }),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpedizioni::route('/'),
            'create' => Pages\CreateSpedizione::route('/create'),
            'edit' => Pages\EditSpedizione::route('/{record}/edit'),
        ];
    }
}
