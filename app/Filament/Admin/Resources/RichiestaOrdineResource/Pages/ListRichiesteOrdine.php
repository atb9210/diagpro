<?php

namespace App\Filament\Admin\Resources\RichiestaOrdineResource\Pages;

use App\Filament\Admin\Resources\RichiestaOrdineResource;
use App\Models\RichiestaOrdine;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRichiesteOrdine extends ListRecords
{
    protected static string $resource = RichiestaOrdineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Non permettiamo la creazione manuale di richieste ordine
            // Queste vengono create solo dai Mini Shop
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'tutte' => Tab::make('Tutte')
                ->badge(RichiestaOrdine::count()),
                
            'in_attesa' => Tab::make('In Attesa Validazione')
                ->modifyQueryUsing(fn (Builder $query) => $query->inAttesa())
                ->badge(RichiestaOrdine::inAttesa()->count())
                ->badgeColor('warning'),
                
            'in_validazione' => Tab::make('In Validazione')
                ->modifyQueryUsing(fn (Builder $query) => $query->inValidazione())
                ->badge(RichiestaOrdine::inValidazione()->count())
                ->badgeColor('info'),
                
            'approvate' => Tab::make('Approvate')
                ->modifyQueryUsing(fn (Builder $query) => $query->approvate())
                ->badge(RichiestaOrdine::approvate()->count())
                ->badgeColor('success'),
                
            'rifiutate' => Tab::make('Rifiutate')
                ->modifyQueryUsing(fn (Builder $query) => $query->rifiutate())
                ->badge(RichiestaOrdine::rifiutate()->count())
                ->badgeColor('danger'),
                
            'convertite' => Tab::make('Convertite')
                ->modifyQueryUsing(fn (Builder $query) => $query->convertite())
                ->badge(RichiestaOrdine::convertite()->count())
                ->badgeColor('primary'),
        ];
    }
}