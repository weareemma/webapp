<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            [
                'name' => 'washing_duration',
                'label' => 'Durata lavaggi (minuti)',
                'type' => 'numeric',
                'desc' => null,
                'value' => 15,
                'validation' => 'required|numeric|min:0'
            ],
            [
                'name' => 'horizon_calendar',
                'label' => 'Orizzonte impostazione calendario (settimane)',
                'type' => 'numeric',
                'desc' => 'Definisci l’intervallo di tempo futuro oltre il quale non sarà possibile prendere un appuntamento',
                'value' => 4,
                'validation' => 'required|numeric|min:0'
            ],
            [
                'name' => 'delete_update_limit',
                'label' => 'Limite annullamento o modifica appuntamento (ore)',
                'type' => 'numeric',
                'desc' => 'Inserisci entro quante ore il cliente potrà modificare o annullare l’appuntamento',
                'value' => 24,
                'validation' => 'required|numeric|min:0'
            ],
            [
                'name' => 'shift_start_tolerance',
                'label' => 'Tolleranza di inizio turno stylist (minuti)',
                'type' => 'numeric',
                'desc' => "Inserisci la tolleranza all'inizio del turno per la quale è possibile assegnare uno stylist ad un appuntamento",
                'value' => 10,
                'validation' => 'required|numeric|min:0'
            ],
            [
                'name' => 'shift_end_tolerance',
                'label' => 'Tolleranza di fine turno stylist (minuti)',
                'type' => 'numeric',
                'desc' => 'Inserisci la tolleranza alla fine del turno per la quale è possibile assegnare uno stylist ad un appuntamento',
                'value' => 10,
                'validation' => 'required|numeric|min:0'
            ],
        ];

        if (Setting::query()->count() == 0)
        {
            $model = new Setting([
                'valid_from' => Carbon::minValue(),
                'valid_to' => Carbon::maxValue()
            ]);
            $model->data = $settings;
            $model->save();
        }
    }
}
