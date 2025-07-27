<?php

namespace App\Filament\Admin\Resources\IntegrazioneResource\Pages;

use App\Filament\Admin\Resources\IntegrazioneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIntegrazioni extends ListRecords
{
    protected static string $resource = IntegrazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
