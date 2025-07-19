<?php

namespace App\Filament\Admin\Resources\ImpostazioneResource\Pages;

use App\Filament\Admin\Resources\ImpostazioneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImpostaziones extends ListRecords
{
    protected static string $resource = ImpostazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
