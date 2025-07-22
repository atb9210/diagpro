<?php

namespace App\Filament\Admin\Resources\CampagnaResource\Pages;

use App\Filament\Admin\Resources\CampagnaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCampagna extends EditRecord
{
    protected static string $resource = CampagnaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Visualizza')
                ->icon('heroicon-o-eye'),
            Actions\DeleteAction::make()
                ->label('Elimina')
                ->icon('heroicon-o-trash'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'Modifica Campagna: ' . $this->record->nome_campagna;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Campagna aggiornata con successo!';
    }
}