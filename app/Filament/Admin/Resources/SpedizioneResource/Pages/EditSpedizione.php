<?php

namespace App\Filament\Admin\Resources\SpedizioneResource\Pages;

use App\Filament\Admin\Resources\SpedizioneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpedizione extends EditRecord
{
    protected static string $resource = SpedizioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
