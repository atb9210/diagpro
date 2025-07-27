<?php

namespace App\Filament\Admin\Resources\IntegrazioneResource\Pages;

use App\Filament\Admin\Resources\IntegrazioneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIntegrazione extends EditRecord
{
    protected static string $resource = IntegrazioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
