<?php

namespace App\Filament\Admin\Resources\OrdiniResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpedizioneRelationManager extends RelationManager
{
    protected static string $relationship = 'spedizione';

    protected static ?string $recordTitleAttribute = 'numero_tracciamento';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dettagli Spedizione')
                    ->schema([
                        Forms\Components\Select::make('corriere')
                            ->label('Corriere')
                            ->options([
                                'brt' => 'BRT',
                                'dhl' => 'DHL',
                                'gls' => 'GLS',
                                'sda' => 'SDA',
                                'altro' => 'Altro',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('numero_tracciamento')
                            ->label('Numero Tracciamento')
                            ->required(),
                        Forms\Components\Select::make('stato')
                            ->label('Stato')
                            ->options([
                                'in_preparazione' => 'In Preparazione',
                                'spedito' => 'Spedito',
                                'in_transito' => 'In Transito',
                                'consegnato' => 'Consegnato',
                                'fallito' => 'Fallito',
                            ])
                            ->required(),
                        Forms\Components\DateTimePicker::make('data_spedizione')
                            ->label('Data Spedizione'),
                        Forms\Components\DateTimePicker::make('data_consegna_prevista')
                            ->label('Data Consegna Prevista'),
                        Forms\Components\DateTimePicker::make('data_consegna_effettiva')
                            ->label('Data Consegna Effettiva'),
                        Forms\Components\Textarea::make('indirizzo_spedizione')
                            ->label('Indirizzo Spedizione')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('note')
                            ->label('Note')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('corriere')
                    ->label('Corriere'),
                Tables\Columns\TextColumn::make('numero_tracciamento')
                    ->label('Numero Tracciamento'),
                Tables\Columns\TextColumn::make('stato')
                    ->label('Stato'),
                Tables\Columns\TextColumn::make('data_spedizione')
                    ->label('Data Spedizione')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('data_consegna_prevista')
                    ->label('Data Consegna Prevista')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('data_consegna_effettiva')
                    ->label('Data Consegna Effettiva')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}