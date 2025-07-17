<?php

namespace Database\Seeders;

use App\Models\TrafficSource;
use Illuminate\Database\Seeder;

class TrafficSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trafficSources = [
            [
                'nome' => 'Facebook Ads',
                'icona' => '',
                'attivo' => true,
            ],
            [
                'nome' => 'Google Ads',
                'icona' => '',
                'attivo' => true,
            ],
            [
                'nome' => 'Ebay',
                'icona' => '',
                'attivo' => true,
            ],
            [
                'nome' => 'Facebook Marketplace',
                'icona' => '',
                'attivo' => true,
            ],
            [
                'nome' => 'Subito',
                'icona' => '',
                'attivo' => true,
            ],
            [
                'nome' => 'Vinted',
                'icona' => '',
                'attivo' => true,
            ],
            [
                'nome' => 'Wallapop',
                'icona' => '',
                'attivo' => true,
            ],
        ];

        foreach ($trafficSources as $source) {
            TrafficSource::create($source);
        }
    }
}
