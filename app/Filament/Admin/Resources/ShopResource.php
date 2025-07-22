<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ShopResource\Pages;
use App\Filament\Admin\Resources\ShopResource\RelationManagers;
use App\Filament\Admin\Resources\ShopResource\RelationManagers\ProdottiRelationManager;
use App\Filament\Admin\Resources\ShopResource\RelationManagers\RichiesteOrdineRelationManager;
use App\Models\Shop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    
    protected static ?string $navigationGroup = 'Marketing';
    
    protected static ?string $navigationLabel = 'Mini Shop';
    
    protected static ?string $modelLabel = 'Shop';
    
    protected static ?string $pluralModelLabel = 'Shop';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informazioni Generali')
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Shop::class, 'slug', ignoreRecord: true)
                            ->rules(['alpha_dash'])
                            ->helperText('URL del shop: /shop/{slug}'),
                        Forms\Components\Textarea::make('descrizione')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('attivo')
                            ->default(true)
                            ->helperText('Disattiva per nascondere il shop dal pubblico'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Branding')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->directory('shops/logos')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\ColorPicker::make('colore_primario')
                            ->helperText('Colore principale del tema'),
                        Forms\Components\ColorPicker::make('colore_secondario')
                            ->helperText('Colore secondario del tema'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('SEO')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->maxLength(60)
                            ->helperText('Titolo per i motori di ricerca (max 60 caratteri)'),
                        Forms\Components\Textarea::make('meta_description')
                            ->maxLength(160)
                            ->rows(3)
                            ->helperText('Descrizione per i motori di ricerca (max 160 caratteri)')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(url('/images/default-shop.png')),
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Slug copiato!')
                    ->formatStateUsing(fn (string $state): string => "/shop/{$state}"),
                Tables\Columns\IconColumn::make('attivo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('prodotti_count')
                    ->counts('prodotti')
                    ->label('Prodotti')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('ordini_count')
                    ->counts('ordini')
                    ->label('Ordini')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('richieste_ordine_count')
                    ->counts('richiesteOrdine')
                    ->label('Richieste')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->label('Creato il')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('attivo')
                    ->label('Stato')
                    ->boolean()
                    ->trueLabel('Solo attivi')
                    ->falseLabel('Solo disattivi')
                    ->native(false),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->url(fn (Shop $record): string => "/shop/{$record->slug}")
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-eye')
                        ->label('Visualizza Shop'),
                    Tables\Actions\Action::make('richieste_ordine')
                        ->label('Richieste Ordine')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->color('info')
                        ->url(fn (Shop $record): string => route('filament.admin.resources.richiesta-ordines.index', [
                            'tableFilters' => [
                                'shop' => [
                                    'value' => $record->id,
                                ],
                            ],
                        ]))
                        ->badge(fn (Shop $record): ?string => $record->richieste_ordine_count ?? $record->richiesteOrdine()->count())
                        ->badgeColor('warning'),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ProdottiRelationManager::class,
            RichiesteOrdineRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShops::route('/'),
            'create' => Pages\CreateShop::route('/create'),
            'edit' => Pages\EditShop::route('/{record}/edit'),
        ];
    }
}
