<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AbbonamentoResource\Pages;
use App\Filament\Admin\Resources\AbbonamentoResource\RelationManagers;
use App\Models\Abbonamento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AbbonamentoResource extends Resource
{
    protected static ?string $model = Abbonamento::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationLabel = 'Abbonamenti';
    
    protected static ?string $modelLabel = 'Abbonamento';
    
    protected static ?string $pluralModelLabel = 'Abbonamenti';
    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('prezzo')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('costo')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('durata')
                    ->numeric()
                    ->suffix(' giorni')
                    ->sortable(),
                Tables\Columns\TextColumn::make('frequenza_rinnovo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'mensile' => 'danger',
                        'trimestrale' => 'warning',
                        'semestrale' => 'success',
                        'annuale' => 'info',
                    }),
                Tables\Columns\IconColumn::make('attivo')
                    ->boolean()
                    ->sortable(),
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
                //
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
            'index' => Pages\ListAbbonamentos::route('/'),
            'create' => Pages\CreateAbbonamento::route('/create'),
            'edit' => Pages\EditAbbonamento::route('/{record}/edit'),
        ];
    }
}
