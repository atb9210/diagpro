<?php

namespace App\Filament\Admin\Resources\CampagnaResource\Pages;

use App\Filament\Admin\Resources\CampagnaResource;
use App\Filament\Admin\Resources\CampagnaResource\Widgets\CampagnaStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCampagnas extends ListRecords
{
    protected static string $resource = CampagnaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuova Campagna')
                ->icon('heroicon-o-plus'),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            CampagnaStatsWidget::class,
        ];
    }
    
    public function getTitle(): string
    {
        return 'Campagne Pubblicitarie';
    }
}