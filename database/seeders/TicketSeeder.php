<?php

namespace Database\Seeders;

use App\Enums\StatutTicket;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $agents = User::query()->where('role', 'SUPPORT')->get();
        $declarants = User::query()->where('role', 'JURIDICTION')->with('juridiction')->get();

        if ($agents->isEmpty() || $declarants->isEmpty()) {
            return;
        }

        $exemplesOuverts = [
            ['BUG', 'Impression du bordereau de transmission impossible', "Depuis la dernière mise à jour du poste, l'impression du bordereau de transmission des requêtes échoue systématiquement avec le message « Accès refusé à l'imprimante »."],
            ['BUG', 'Numéro RG en double sur les enrôlements PL', "Deux enrôlements effectués le même jour ont reçu le même numéro RG. Les deux dossiers sont visibles dans le répertoire mais un seul s'ouvre correctement."],
            ['PLAINTE', "Lenteur extrême à l'ouverture des dossiers", "L'écran de consultation des dossiers met plus de deux minutes à s'afficher aux heures de pointe, ce qui bloque le travail du greffe."],
            ['INCIDENT', 'Blocage complet du module des petits litiges', "Le module des petits litiges ne répond plus depuis ce matin pour aucun agent. L'application affiche un écran vide après la connexion."],
            ['DEMANDE', "Réinitialisation du mot de passe d'un agent", "L'agent nouvellement affecté au greffe civil n'arrive pas à se connecter à son poste. Merci de réinitialiser son mot de passe."],
            ['DEMANDE', "Ajout d'une nouvelle chambre d'audience", "Merci d'ajouter la chambre commerciale n° 4 dans le référentiel des chambres pour les audiences de référé."],
        ];

        foreach ($exemplesOuverts as $index => $exemple) {
            $declarant = $declarants[$index % $declarants->count()];

            $ticket = Ticket::factory()
                ->declarePar($declarant)
                ->create([
                    'type' => $exemple[0],
                    'titre' => $exemple[1],
                    'description' => $exemple[2],
                    'created_at' => now()->subHours(($index + 1) * 7),
                ]);

            // Quelques demandes déjà prises en charge par le support.
            if ($index % 2 === 0) {
                $ticket->affecterA($agents[$index % $agents->count()], $agents[$index % $agents->count()], 'Prise en charge par le support.');

                if ($index % 4 === 0) {
                    $ticket->changerStatut(StatutTicket::EN_COURS, $agents[$index % $agents->count()], 'Diagnostic en cours.');
                }
            }

            $ticket->commentaires()->create([
                'user_id' => $declarant->id,
                'message' => 'Précision : le problème se produit sur tous les postes du greffe, pas seulement sur le mien.',
            ]);
        }

        $exemplesResolus = [
            ['BUG', 'Erreur de calcul des frais de greffe', 'Le calcul des frais de greffe était erroné sur les formalités de type C2. Correctif appliqué et vérifié avec le comptable.'],
            ['PLAINTE', 'Affichage tronqué des noms longs', 'Les noms de personnes morales de plus de 60 caractères étaient tronqués à l’impression. Augmentation de la zone et régénération des modèles.'],
            ['DEMANDE', 'Formation des nouveaux greffiers', 'Session de formation réalisée sur la saisie des enrôlements et la génération du plumitif. Supports transmis.'],
        ];

        foreach ($exemplesResolus as $index => $exemple) {
            $declarant = $declarants[($index + 2) % $declarants->count()];
            $agent = $agents[$index % $agents->count()];

            $ticket = Ticket::factory()
                ->declarePar($declarant)
                ->create([
                    'type' => $exemple[0],
                    'titre' => $exemple[1],
                    'description' => "Demande d'exemple : ".$exemple[1].'.',
                    'created_at' => now()->subDays(4 + $index),
                ]);

            $ticket->affecterA($agent, $agent);
            $ticket->resoudre($agent, $exemple[2]);
        }
    }
}
