<?php

namespace App\Filament\Admin\Resources\AbbonamentoResource\Pages;

use App\Filament\Admin\Resources\AbbonamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAbbonamento extends EditRecord
{
    protected static string $resource = AbbonamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
