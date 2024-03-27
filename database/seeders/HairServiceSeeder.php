<?php

namespace Database\Seeders;

use App\Models\HairService;
use App\Services\IpraticoService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HairServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        HairService::updateOrCreate([
            'title' => 'Lavaggio e piega',
            'level' => 'primary',
            'type' => null,
            'net_price' => 15.0,
            'execution_time' => 30,
            'active' => true,
            'dry_style' => false,
            'afro' => false,
        ]);
        HairService::updateOrCreate([
            'title' => 'Solo piega',
            'level' => 'primary',
            'type' => null,
            'net_price' => 15.0,
            'execution_time' => 20,
            'active' => true,
            'dry_style' => true,
            'afro' => false,
        ]);
        HairService::updateOrCreate([
            'title' => 'Massaggio rilassante',
            'level' => 'addon',
            'type' => 'massage',
            'net_price' => 10.0,
            'execution_time' => 10,
            'active' => true,
            'dry_style' => false,
            'afro' => false,
        ]);
        HairService::updateOrCreate([
            'title' => 'Massaggio energizzante',
            'level' => 'addon',
            'type' => 'massage',
            'net_price' => 10.0,
            'execution_time' => 10,
            'active' => true,
            'dry_style' => false,
            'afro' => false,
        ]);
        HairService::updateOrCreate([
            'title' => 'Treccia',
            'level' => 'addon',
            'type' => 'updo',
            'net_price' => 10.0,
            'execution_time' => 10,
            'active' => true,
            'dry_style' => false,
            'afro' => false,
        ]);
        HairService::updateOrCreate([
            'title' => 'Acconciatura',
            'level' => 'addon',
            'type' => 'updo',
            'net_price' => 10.0,
            'execution_time' => 10,
            'active' => true,
            'dry_style' => false,
            'afro' => false,
        ]);
        HairService::updateOrCreate([
            'title' => 'Trattamento detossinante',
            'level' => 'addon',
            'type' => 'treatment',
            'net_price' => 18.0,
            'execution_time' => 20,
            'active' => true,
            'dry_style' => false,
            'afro' => false,
        ]);
        HairService::updateOrCreate([
            'title' => 'Trattamento lucidante',
            'level' => 'addon',
            'type' => 'treatment',
            'net_price' => 8.0,
            'execution_time' => 5,
            'active' => true,
            'dry_style' => false,
            'afro' => false,
        ]);
        HairService::updateOrCreate([
            'title' => 'Trattamento ricondizionante',
            'level' => 'addon',
            'type' => 'treatment',
            'net_price' => 15.0,
            'execution_time' => 10,
            'active' => true,
            'dry_style' => false,
            'afro' => false,
        ]);
        HairService::updateOrCreate([
            'title' => 'Trattamento ristrutturante',
            'level' => 'addon',
            'type' => 'treatment',
            'net_price' => 18.0,
            'execution_time' => 5,
            'active' => true,
            'dry_style' => false,
            'afro' => false,
        ]);
        HairService::updateOrCreate([
            'title' => 'Sciroppo all\'Hennè',
            'level' => 'addon',
            'type' => 'treatment',
            'net_price' => 20.0,
            'execution_time' => 20,
            'active' => true,
            'dry_style' => false,
            'afro' => false,
        ]);
        HairService::updateOrCreate([
            'title' => 'Correttore colore',
            'level' => 'addon',
            'type' => 'treatment',
            'net_price' => 20.0,
            'execution_time' => 20,
            'active' => true,
            'dry_style' => false,
            'afro' => false,
        ]);
        HairService::updateOrCreate([
            'title' => 'Peeling',
            'level' => 'addon',
            'type' => 'treatment',
            'net_price' => 15.0,
            'execution_time' => 15,
            'active' => true,
            'dry_style' => false,
            'afro' => false,
        ]);

        // (new IpraticoService())->createPriceList();
        // (new IpraticoService())->createCategories();
        // (new IpraticoService())->createProducts();
    }
}
