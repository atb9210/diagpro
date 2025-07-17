<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TrafficSourceResource\Pages;
use App\Filament\Admin\Resources\TrafficSourceResource\RelationManagers;
use App\Models\TrafficSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TrafficSourceResource extends Resource
{
    protected static ?string $model = TrafficSource::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    
    protected static ?string $navigationLabel = 'Fonti di Traffico';
    
    protected static ?string $modelLabel = 'Fonte di Traffico';
    
    protected static ?string $pluralModelLabel = 'Fonti di Traffico';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informazioni Fonte di Traffico')
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('icona')
                            ->label('Icona')
                            ->helperText('Carica un\'icona in formato SVG, PNG o JPG')
                            ->image()
                            ->imageEditor()
                            ->directory('icons')
                            ->visibility('public')
                            ->maxSize(1024)
                            ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/jpeg'])
                            ->downloadable(),
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('icona')
                    ->label('Icona')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-icon.svg'))
                    ->size(40),
                Tables\Columns\IconColumn::make('attivo')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clienti_count')
                    ->label('Numero Clienti')
                    ->counts('clienti')
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
            'index' => Pages\ListTrafficSources::route('/'),
            'create' => Pages\CreateTrafficSource::route('/create'),
            'edit' => Pages\EditTrafficSource::route('/{record}/edit'),
        ];
    }
}
