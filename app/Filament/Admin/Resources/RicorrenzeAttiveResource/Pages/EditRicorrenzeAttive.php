<?php

namespace App\Filament\Admin\Resources\RicorrenzeAttiveResource\Pages;

use App\Filament\Admin\Resources\RicorrenzeAttiveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRicorrenzeAttive extends EditRecord
{
    protected static string $resource = RicorrenzeAttiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
