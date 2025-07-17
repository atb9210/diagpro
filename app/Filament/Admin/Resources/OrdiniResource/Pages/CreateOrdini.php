<?php

namespace App\Filament\Admin\Resources\OrdiniResource\Pages;

use App\Filament\Admin\Resources\OrdiniResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

use Illuminate\Database\Eloquent\Model;

class CreateOrdini extends CreateRecord
{
    protected static string $resource = OrdiniResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Separate repeater data from the main order data
        $prodottiData = $data['prodotti'] ?? [];
        $abbonamentiData = $data['abbonamenti'] ?? [];
        unset($data['prodotti'], $data['abbonamenti']);

        // Create the main order record
        $record = static::getResource()::getModel()::create($data);

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
        if (!empty($prodottiToSync)) {
            $record->prodotti()->sync($prodottiToSync);
        }

        $abbonamentiToSync = [];
        foreach ($abbonamentiData as $abbonamento) {
            if (empty($abbonamento['abbonamento_id'])) continue;
            $abbonamentiToSync[$abbonamento['abbonamento_id']] = [
                'prezzo' => $abbonamento['prezzo'],
                'data_inizio' => $abbonamento['data_inizio'],
                'costo' => $abbonamento['costo'],
            ];
        }
        if (!empty($abbonamentiToSync)) {
            $record->abbonamenti()->sync($abbonamentiToSync);
        }

        // Manually call the calculation method from the model
        $record->calculateAndSaveCostoProdotto();

        return $record;
    }
}
