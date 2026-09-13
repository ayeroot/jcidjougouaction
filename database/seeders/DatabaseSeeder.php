<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\{Mandat, Membre, Carriere, Cotisation, Contribution, Depense, User, Postulant, Projet, Partenaire, Formateur, Formation, Presence, RapportFormation};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $mail = fn (string $prenom, string $nom) =>
            Str::of("$prenom $nom")->ascii()->lower()->replace(' ', '.').'@example.bj';

        /* Rôles */
        $roles = ['membre', 'president', 'vpe', 'vpre', 'vpf', 'vpm', 'vpcd', 'vp_projet', 'tresorier', 'secretaire'];
        foreach ($roles as $r) { Role::firstOrCreate(['name' => $r]); }

        /* Mandat actif */
        $mandat = Mandat::firstOrCreate(
            ['annee' => '2026'],
            ['theme' => 'Impact & Leadership', 'date_debut' => '2026-01-01', 'date_fin' => '2026-12-31', 'actif' => true]
        );

        /* ---- Comptes du CDL : identifiés par la FONCTION (pas de nom de personne) ----
           [login, fonction, rôle, honorable] — email = login@jcidjougou.bj */
        $cdl = [
            ['president.local',  'Président Local',                'president',  true],
            ['vpe.local',        'VP Exécutive',                   'vpe',        true],
            ['vpre.local',       'VP Relations Extérieures',       'vpre',       false],
            ['vpf.local',        'VP Formations',                  'vpf',        true],
            ['vpm.local',        'VP Management',                  'vpm',        true],
            ['vpcd.local',       'VP Croissance & Développement',  'vpcd',       true],
            ['vp-projet.local',  'VP Projet & Thème principal',    'vp_projet',  false],
            ['tresorier.local',  'Trésorier Général',              'tresorier',  true],
            ['secretaire.local', 'Secrétaire Général',             'secretaire', true],
        ];
        foreach ($cdl as [$login, $fonction, $role, $honorable]) {
            // Le "membre" du CDL porte la fonction comme identité (nom = fonction, pas de prénom).
            $membre = Membre::firstOrCreate(
                ['fonction' => $fonction, 'nom' => $fonction],
                ['prenom' => '', 'ville' => 'Djougou', 'statut' => 'actif', 'date_adhesion' => '2026-01-01',
                 'email' => "$login@jcidjougou.bj",
                 'telephone' => '+229 01 '.rand(40,99).' '.rand(10,99).' '.rand(10,99).' '.rand(10,99)]
            );
            Carriere::firstOrCreate(['membre_id' => $membre->id, 'type' => $fonction],
                ['annee' => '2026', 'description' => 'Mandat en cours']);
            if ($honorable) {
                Cotisation::firstOrCreate(['membre_id' => $membre->id, 'mandat_id' => $mandat->id],
                    ['montant' => 15000, 'date_cotisation' => '2026-02-15']);
            }
            $user = User::firstOrCreate(['email' => "$login@jcidjougou.bj"],
                ['name' => $fonction, 'password' => 'password', 'membre_id' => $membre->id]);
            $user->syncRoles([$role]);
        }

        /* ---- Membres simples (personnes réelles) ---- */
        $simples = [
            ['Aline', 'Doko', 'F', 1998, true],
            ['Karim', 'Orou', 'M', 2000, false],
            ['Chantal', 'Bagri', 'F', 1985, true],
            ['Moussa', 'Tamou', 'M', 1979, false],
        ];
        foreach ($simples as [$prenom, $nom, $sexe, $an, $honorable]) {
            $membre = Membre::firstOrCreate(['nom' => $nom, 'prenom' => $prenom],
                ['sexe' => $sexe, 'date_naissance' => "$an-03-10", 'ville' => 'Djougou',
                 'fonction' => 'Membre', 'statut' => 'actif', 'date_adhesion' => '2025-02-01',
                 'telephone' => '+229 01 '.rand(40,99).' '.rand(10,99).' '.rand(10,99).' '.rand(10,99),
                 'email' => $mail($prenom, $nom)]);
            if ($honorable) {
                Cotisation::firstOrCreate(['membre_id' => $membre->id, 'mandat_id' => $mandat->id],
                    ['montant' => 15000, 'date_cotisation' => '2026-03-01']);
            }
            $user = User::firstOrCreate(['email' => $membre->email],
                ['name' => $membre->nom_complet, 'password' => 'password', 'membre_id' => $membre->id]);
            $user->syncRoles(['membre']);
        }

        /* ---- Postulants ---- */
        $postulants = [
            ['Delphine', 'Assogba', 'F', 'nouveau'],
            ['Roland',   'Kpade',   'M', 'contacte'],
            ['Sylvie',   'Baba',    'F', 'en_formation'],
            ['Aristide', 'Gado',    'M', 'en_formation'],
            ['Rita',     'Sanni',   'F', 'admis'],
            ['Yves',     'Dossou',  'M', 'rejete'],
        ];
        foreach ($postulants as [$prenom, $nom, $sexe, $statut]) {
            Postulant::firstOrCreate(['nom' => $nom, 'prenom' => $prenom],
                ['sexe' => $sexe, 'ville' => 'Djougou', 'statut' => $statut,
                 'telephone' => '+229 01 '.rand(40,99).' '.rand(10,99).' '.rand(10,99).' '.rand(10,99),
                 'email' => $mail($prenom, $nom),
                 'motivation' => "Je souhaite m'engager pour le developpement de ma communaute.",
                 'date_naissance' => rand(1998,2004).'-0'.rand(1,9).'-1'.rand(0,9)]);
        }

        /* ---- Formations + formateur + présences + rapport ---- */
        $vpf = Membre::where('fonction', 'VP Formations')->first();
        $formateur = Formateur::firstOrCreate(['nom' => 'Formateur interne (VPF)'],
            ['type' => 'interne', 'membre_id' => $vpf?->id, 'contact' => 'formation@jcidjougou.bj']);
        $formateurExt = Formateur::firstOrCreate(['nom' => 'Cabinet Élan Conseil'],
            ['type' => 'externe', 'contact' => 'contact@elanconseil.bj']);

        $f1 = Formation::firstOrCreate(['titre' => 'Introduction a la JCI'],
            ['theme' => 'Decouverte du mouvement', 'objectifs' => 'Comprendre la mission, la vision et les valeurs de la JCI.',
             'date_formation' => '2026-09-05 09:00', 'lieu' => 'Siege JCI Djougou', 'statut' => 'realisee',
             'mandat_id' => $mandat->id, 'formateur_id' => $formateur->id]);
        $f2 = Formation::firstOrCreate(['titre' => 'Techniques de prise de parole'],
            ['theme' => 'Leadership', 'objectifs' => "S'exprimer en public avec aisance et conviction.",
             'date_formation' => '2026-09-20 09:00', 'lieu' => 'Siege JCI Djougou', 'statut' => 'planifiee',
             'mandat_id' => $mandat->id, 'formateur_id' => $formateurExt->id]);

        foreach (Postulant::whereIn('statut', ['en_formation', 'admis'])->get() as $post) {
            Presence::firstOrCreate(['formation_id' => $f1->id, 'postulant_id' => $post->id], ['present' => true]);
            Presence::firstOrCreate(['formation_id' => $f2->id, 'postulant_id' => $post->id], ['present' => (bool) rand(0,1)]);
        }
        RapportFormation::firstOrCreate(['formation_id' => $f1->id],
            ['contenu' => "La séance a réuni les postulants autour des fondamentaux de la JCI. "
                        . "Bonne participation ; les échanges ont porté sur l'engagement citoyen et le leadership."]);

        /* ---- Projets ---- */
        $projetsRefs = [];
        foreach ([
            ['Reboisement scolaire', "Plantation d'arbres dans 5 ecoles de Djougou.", 'en_cours', 60],
            ['Don de sang citoyen', 'Campagne de collecte avec le centre de sante.', 'termine', 100],
            ['Alphabetisation numerique', 'Initiation a l informatique pour les jeunes.', 'a_venir', 10],
        ] as [$titre, $desc, $statut, $av]) {
            $projetsRefs[$titre] = Projet::firstOrCreate(['titre' => $titre],
                ['description' => $desc, 'statut' => $statut, 'avancement' => $av, 'public' => true, 'mandat_id' => $mandat->id]);
        }

        /* ---- Partenaires ---- */
        $partRefs = [];
        foreach ([['Mairie de Djougou', 'Institution'], ['Radio Donga', 'Media'], ['MDE Informatique & Reseaux', 'Entreprise']] as [$nom, $type]) {
            $partRefs[$nom] = Partenaire::firstOrCreate(['nom' => $nom], ['type' => $type, 'public' => true]);
        }

        /* ---- Finances : contributions & dépenses de démonstration ---- */
        Contribution::firstOrCreate(
            ['source' => 'Mairie de Djougou', 'projet_id' => $projetsRefs['Reboisement scolaire']->id],
            ['montant' => 200000, 'date_contribution' => '2026-04-10', 'partenaire_id' => $partRefs['Mairie de Djougou']->id]);
        Contribution::firstOrCreate(
            ['source' => 'Radio Donga', 'projet_id' => null],
            ['montant' => 50000, 'date_contribution' => '2026-05-02', 'partenaire_id' => $partRefs['Radio Donga']->id]);

        Depense::firstOrCreate(['libelle' => 'Achat de plants', 'projet_id' => $projetsRefs['Reboisement scolaire']->id],
            ['montant' => 120000, 'date_depense' => '2026-04-20', 'mandat_id' => $mandat->id]);
        Depense::firstOrCreate(['libelle' => 'Fournitures de bureau', 'projet_id' => null],
            ['montant' => 30000, 'date_depense' => '2026-05-15', 'mandat_id' => $mandat->id]);
    }
}
