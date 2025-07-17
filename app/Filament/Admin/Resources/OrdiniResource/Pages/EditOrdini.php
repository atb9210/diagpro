<?php

namespace App\Filament\Admin\Resources\OrdiniResource\Pages;

use App\Filament\Admin\Resources\OrdiniResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Illuminate\Database\Eloquent\Model;

class EditOrdini extends EditRecord
{
    protected static string $resource = OrdiniResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        // Load products and format for the repeater
        $prodottiData = $record->prodotti->map(function ($prodotto) {
            return [
                'prodotto_id' => $prodotto->id,
                'quantita' => $prodotto->pivot->quantita,
                'prezzo_unitario' => $prodotto->pivot->prezzo_unitario,
                'costo' => $prodotto->pivot->costo,
            ];
        })->toArray();

        // Load subscriptions and format for the repeater
        $abbonamentiData = $record->abbonamenti->map(function ($abbonamento) {
            return [
                'abbonamento_id' => $abbonamento->id,
                'prezzo' => $abbonamento->pivot->prezzo,
                'data_inizio' => $abbonamento->pivot->data_inizio,
                'costo' => $abbonamento->pivot->costo,
            ];
        })->toArray();

        $data['prodotti'] = $prodottiData;
        $data['abbonamenti'] = $abbonamentiData;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Separate repeater data from the main order data
        $prodottiData = $data['prodotti'] ?? [];
        $abbonamentiData = $data['abbonamenti'] ?? [];
        unset($data['prodotti'], $data['abbonamenti']);

        // Update the main order record
        $record->update($data);

        // Manually sync the relationships
        $prodottiToSync = [];
        foreach ($prodottiData as $prodotto) {
            if (empty($prodotto['prodotto_id'])) continue;
            $prodottiToSync[$prodotto['prodotto_id']] = [
                'quantita' => $prodotto['quantita'],
                'prezzo_unitario' => $prodotto['prezzo_unitario'],
                'costo' => $prodotto['costo'],
            ];
        }
        $record->prodotti()->sync($prodottiToSync);

        $abbonamentiToSync = [];
        foreach ($abbonamentiData as $abbonamento) {
            if (empty($abbonamento['abbonamento_id'])) continue;
            $abbonamentiToSync[$abbonamento['abbonamento_id']] = [
                'prezzo' => $abbonamento['prezzo'],
                'data_inizio' => $abbonamento['data_inizio'],
                'costo' => $abbonamento['costo'],
            ];
        }
        $record->abbonamenti()->sync($abbonamentiToSync);

        // Manually call the calculation method from the model
        $record->calculateAndSaveCostoProdotto();

        return $record;
    }
}
