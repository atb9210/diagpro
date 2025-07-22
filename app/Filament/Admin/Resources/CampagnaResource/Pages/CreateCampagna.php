<?php

namespace App\Filament\Admin\Resources\CampagnaResource\Pages;

use App\Filament\Admin\Resources\CampagnaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCampagna extends CreateRecord
{
    protected static string $resource = CampagnaResource::class;
    
    public function getTitle(): string
    {
        return 'Crea Nuova Campagna';
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Campagna creata con successo!';
    }
}