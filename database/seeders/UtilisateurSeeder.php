<?php

namespace Database\Seeders;

use App\Models\Juridiction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->administrateur()->create([
            'name' => 'Administrateur Support',
            'email' => 'admin@support.local',
            'password' => 'Support1234',
            'fonction' => 'Chef du support applicatif',
        ]);

        $agents = [
            ['name' => 'Awa Koné', 'email' => 'awa.kone@support.local'],
            ['name' => 'Yao Kouassi', 'email' => 'yao.kouassi@support.local'],
        ];

        foreach ($agents as $agent) {
            User::factory()->support()->create($agent + [
                'password' => 'Support1234',
                'fonction' => 'Agent de support',
            ]);
        }

        foreach (Juridiction::query()->where('actif', true)->get() as $juridiction) {
            User::factory()->create([
                'name' => 'Contact '.$juridiction->libelle,
                'email' => 'contact-'.Str::slug($juridiction->code).'@juridictions.local',
                'password' => 'Support1234',
                'juridiction_id' => $juridiction->id,
                'fonction' => 'Greffier en chef',
            ]);
        }
    }
}
