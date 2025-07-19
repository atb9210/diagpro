<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FornitoreResource\Pages;
use App\Filament\Admin\Resources\FornitoreResource\RelationManagers;
use App\Models\Fornitore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FornitoreResource extends Resource
{
    protected static ?string $model = Fornitore::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    
    protected static ?string $navigationLabel = 'Fornitori';
    
    protected static ?string $modelLabel = 'Fornitore';
    
    protected static ?string $pluralModelLabel = 'Fornitori';
    
    protected static ?string $navigationGroup = 'Impostazioni';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('descrizione')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('logo')
                    ->image()
                    ->imageEditor()
                    ->directory('fornitori/loghi')
                    ->visibility('public'),
                Forms\Components\TextInput::make('link_sito')
                    ->url()
                    ->maxLength(255)
                    ->label('Sito Web'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('telefono')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Textarea::make('indirizzo')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('attivo')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('attivo')
                    ->boolean(),
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
            'index' => Pages\ListFornitores::route('/'),
            'create' => Pages\CreateFornitore::route('/create'),
            'edit' => Pages\EditFornitore::route('/{record}/edit'),
        ];
    }
}
