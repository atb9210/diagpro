<?php

namespace App\Filament\Admin\Resources\ProdottoResource\Pages;

use App\Filament\Admin\Resources\ProdottoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProdottos extends ListRecords
{
    protected static string $resource = ProdottoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
