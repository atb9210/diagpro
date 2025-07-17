<?php

namespace App\Filament\Admin\Resources\AbbonamentoResource\Pages;

use App\Filament\Admin\Resources\AbbonamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAbbonamentos extends ListRecords
{
    protected static string $resource = AbbonamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
