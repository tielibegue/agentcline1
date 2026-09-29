<?php

namespace Database\Seeders;

use App\Models\Juridiction;
use Illuminate\Database\Seeder;

class JuridictionSeeder extends Seeder
{
    public function run(): void
    {
        $structures = [
            [
                'code' => 'TCA-ABJ',
                'libelle' => "Tribunal de commerce d'Abidjan",
                'type' => 'TRIBUNAL_COMMERCE',
                'ville' => 'Abidjan',
                'commune' => 'Plateau',
            ],
            [
                'code' => 'TCA-YOP',
                'libelle' => 'Tribunal de commerce de Yopougon',
                'type' => 'TRIBUNAL_COMMERCE',
                'ville' => 'Abidjan',
                'commune' => 'Yopougon',
            ],
            [
                'code' => 'TCA-BKE',
                'libelle' => 'Tribunal de commerce de Bouaké',
                'type' => 'TRIBUNAL_COMMERCE',
                'ville' => 'Bouaké',
                'commune' => 'Bouaké',
            ],
            [
                'code' => 'CA-ABJ',
                'libelle' => "Cour d'appel d'Abidjan",
                'type' => 'COUR_APPEL',
                'ville' => 'Abidjan',
                'commune' => 'Plateau',
            ],
            [
                'code' => 'TPI-YAK',
                'libelle' => 'Tribunal de première instance de Yamoussoukro',
                'type' => 'TRIBUNAL_PREMIERE_INSTANCE',
                'ville' => 'Yamoussoukro',
                'commune' => 'Yamoussoukro',
            ],
            [
                'code' => 'AJ-TRESOR',
                'libelle' => 'Agent judiciaire du Trésor',
                'type' => 'AUTRE',
                'ville' => 'Abidjan',
                'commune' => 'Plateau',
            ],
        ];

        foreach ($structures as $structure) {
            Juridiction::query()->updateOrCreate(
                ['code' => $structure['code']],
                $structure + ['actif' => true],
            );
        }
    }
}
