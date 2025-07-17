<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Ordini;
use App\Models\Prodotto;
use Illuminate\Console\Command;

class CreateOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new order with a product';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cliente = Cliente::first();
        if (!$cliente) {
            $this->error('No client found. Please create a client first.');
            return;
        }

        $prodotto = Prodotto::first();
        if (!$prodotto) {
            $this->error('No product found. Please create a product first.');
            return;
        }

        $ordine = Ordini::create([
            'cliente_id' => $cliente->id,
            'data' => now(),
            'stato' => 'in_lavorazione',
            'prezzo_vendita' => 0,
            'costo_spedizione' => 10.00,
            'note' => 'Test order created from command',
        ]);

        $ordine->prodotti()->attach($prodotto->id, ['quantita' => 2, 'prezzo_unitario' => $prodotto->prezzo]);

        $ordine->calculateAndSaveCostoProdotto();

        $this->info('Order created successfully with ID: ' . $ordine->id);

        $newOrder = Ordini::find($ordine->id);
        $this->info('Order details:');
        $this->info('ID: ' . $newOrder->id);
        $this->info('Costo Prodotto: ' . $newOrder->costo_prodotto);
    }
}
