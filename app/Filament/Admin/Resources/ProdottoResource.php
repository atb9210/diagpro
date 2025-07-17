<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProdottoResource\Pages;
use App\Filament\Admin\Resources\ProdottoResource\RelationManagers;
use App\Models\Prodotto;
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
                        Forms\Components\TextInput::make('prezzo')
                            ->required()
                            ->numeric()
                            ->prefix('€'),
                        Forms\Components\TextInput::make('costo')
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
                    ])->columns(2),
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
                Tables\Columns\TextColumn::make('prezzo')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('costo')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantita_disponibile')
                    ->numeric()
                    ->sortable()
                    ->placeholder('N/A'),
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
            'index' => Pages\ListProdottos::route('/'),
            'create' => Pages\CreateProdotto::route('/create'),
            'edit' => Pages\EditProdotto::route('/{record}/edit'),
        ];
    }
}
