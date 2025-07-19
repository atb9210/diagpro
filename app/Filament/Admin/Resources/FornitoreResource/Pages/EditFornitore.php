<?php

namespace App\Filament\Admin\Resources\FornitoreResource\Pages;

use App\Filament\Admin\Resources\FornitoreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFornitore extends EditRecord
{
    protected static string $resource = FornitoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
