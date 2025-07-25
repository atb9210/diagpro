<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ImpostazioneResource\Pages;
use App\Filament\Admin\Resources\ImpostazioneResource\RelationManagers;
use App\Models\Impostazione;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImpostazioneResource extends Resource
{
    protected static ?string $model = Impostazione::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'Impostazioni';
    
    protected static ?string $modelLabel = 'Impostazione';
    
    protected static ?string $pluralModelLabel = 'Impostazioni';
    
    protected static ?int $navigationSort = 99;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dettagli Impostazione')
                    ->schema([
                        Forms\Components\TextInput::make('chiave')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Identificativo univoco dell\'impostazione'),
                        Forms\Components\Select::make('tipo')
                            ->options([
                                'string' => 'Testo',
                                'number' => 'Numero',
                                'boolean' => 'Vero/Falso',
                                'json' => 'JSON',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('valore', '')),
                        Forms\Components\TextInput::make('descrizione')
                            ->maxLength(255)
                            ->helperText('Descrizione dell\'impostazione'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Valore')
                    ->schema([
                        Forms\Components\TextInput::make('valore')
                            ->label('Valore')
                            ->required()
                            ->visible(fn (Forms\Get $get) => in_array($get('tipo'), ['string']))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('valore')
                            ->label('Valore Numerico')
                            ->required()
                            ->numeric()
                            ->visible(fn (Forms\Get $get) => $get('tipo') === 'number'),
                        Forms\Components\Toggle::make('valore')
                            ->label('Valore Booleano')
                            ->required()
                            ->visible(fn (Forms\Get $get) => $get('tipo') === 'boolean'),
                        Forms\Components\Textarea::make('valore')
                            ->label('Valore JSON')
                            ->required()
                            ->visible(fn (Forms\Get $get) => $get('tipo') === 'json')
                            ->helperText('Inserire un JSON valido'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('chiave')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('tipo')
                    ->colors([
                        'primary' => 'string',
                        'success' => 'number',
                        'warning' => 'boolean',
                        'info' => 'json',
                    ]),
                Tables\Columns\TextColumn::make('valore')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('descrizione')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'string' => 'Testo',
                        'number' => 'Numero',
                        'boolean' => 'Vero/Falso',
                        'json' => 'JSON',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListImpostaziones::route('/'),
            'create' => Pages\CreateImpostazione::route('/create'),
            'edit' => Pages\EditImpostazione::route('/{record}/edit'),
        ];
    }
}
