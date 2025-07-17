<?php

namespace App\Filament\Admin\Resources\ProdottoResource\Pages;

use App\Filament\Admin\Resources\ProdottoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProdotto extends EditRecord
{
    protected static string $resource = ProdottoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
