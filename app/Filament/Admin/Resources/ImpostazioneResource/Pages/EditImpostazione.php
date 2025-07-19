<?php

namespace App\Filament\Admin\Resources\ImpostazioneResource\Pages;

use App\Filament\Admin\Resources\ImpostazioneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImpostazione extends EditRecord
{
    protected static string $resource = ImpostazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
