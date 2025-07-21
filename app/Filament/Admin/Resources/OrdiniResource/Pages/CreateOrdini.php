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
            
            // Calcola la data di scadenza basata sulla durata dell'abbonamento moltiplicata per la quantità
            $abbonamentoModel = \App\Models\Abbonamento::find($abbonamento['abbonamento_id']);
            $dataScadenza = null;
            if ($abbonamentoModel && isset($abbonamento['data_inizio'])) {
                $dataInizio = new \DateTime($abbonamento['data_inizio']);
                $quantita = isset($abbonamento['quantita']) ? (int)$abbonamento['quantita'] : 1;
                $durataGiorni = $abbonamentoModel->durata * $quantita;
                $dataScadenza = $dataInizio->modify('+' . $durataGiorni . ' days')->format('Y-m-d');
            }
            
            $abbonamentiToSync[$abbonamento['abbonamento_id']] = [
                'quantita' => isset($abbonamento['quantita']) ? $abbonamento['quantita'] : 1,
                'prezzo' => $abbonamento['prezzo'],
                'data_inizio' => $abbonamento['data_inizio'],
                'data_fine' => $dataScadenza,
                'costo' => $abbonamento['costo'],
                'attivo' => true,
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
