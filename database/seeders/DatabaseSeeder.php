<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\{Mandat, Membre, Carriere, Cotisation, Contribution, Depense, User, Postulant,
                Projet, Partenaire, Formateur, Formation, Presence, RapportFormation,
                Archive, Standard, PlanAction};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $mail = fn (string $prenom, string $nom) =>
            Str::of("$prenom $nom")->ascii()->lower()->replace(' ', '.').'@example.bj';

        /* Rôles */
        foreach (['admin','membre','president','vpe','vpre','vpf','vpm','vpcd','vp_projet','tresorier','secretaire'] as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        /* ---- Administrateur (gère les comptes et attribue les rôles) ---- */
        $admin = User::firstOrCreate(['email' => 'admin@jcidjougou.bj'],
            ['name' => 'Administrateur', 'password' => 'password']);
        $admin->syncRoles(['admin']);

        /* Mandat actif */
        $mandat = Mandat::firstOrCreate(['annee' => '2026'],
            ['theme' => 'Impact & Leadership', 'couleur' => '#0891b2', 'date_debut' => '2026-01-01', 'date_fin' => '2026-12-31', 'actif' => true]);

        /* ---- CDL : identifiés par la fonction, avec date de naissance ---- */
        // [login, fonction, rôle, honorable, date_naissance]
        $cdl = [
            ['president.local',  'Président Local',               'president',  true,  '1992-09-08'],
            ['vpe.local',        'VP Exécutive',                  'vpe',        true,  '1995-03-21'],
            ['vpre.local',       'VP Relations Extérieures',      'vpre',       false, '1990-11-14'],
            ['vpf.local',        'VP Formations',                 'vpf',        true,  '1994-09-25'],
            ['vpm.local',        'VP Management',                 'vpm',        true,  '1993-06-02'],
            ['vpcd.local',       'VP Croissance & Développement', 'vpcd',       true,  '1996-01-30'],
            ['vp-projet.local',  'VP Projet & Thème principal',   'vp_projet',  false, '1991-07-19'],
            ['tresorier.local',  'Trésorier Général',             'tresorier',  true,  '1997-04-11'],
            ['secretaire.local', 'Secrétaire Général',            'secretaire', true,  '1989-12-05'],
        ];
        foreach ($cdl as [$login, $fonction, $role, $honorable, $dob]) {
            $membre = Membre::firstOrCreate(
                ['fonction' => $fonction, 'nom' => $fonction],
                ['prenom' => '', 'ville' => 'Djougou', 'statut' => 'actif', 'date_adhesion' => '2026-01-01',
                 'date_naissance' => $dob, 'email' => "$login@jcidjougou.bj",
                 'telephone' => '+229 01 '.rand(40,99).' '.rand(10,99).' '.rand(10,99).' '.rand(10,99)]);
            Carriere::firstOrCreate(['membre_id' => $membre->id, 'type' => 'Bureau'],
                ['annee' => '2026', 'description' => $fonction]);
            if ($honorable) {
                Cotisation::firstOrCreate(['membre_id' => $membre->id, 'mandat_id' => $mandat->id],
                    ['montant' => 15000, 'date_cotisation' => '2026-02-15']);
            }
            $user = User::firstOrCreate(['email' => "$login@jcidjougou.bj"],
                ['name' => $fonction, 'password' => 'password', 'membre_id' => $membre->id]);
            $user->syncRoles([$role]);
        }

        /* ---- Membres simples : statuts, âges et carrières variés (pour les filtres) ---- */
        // [prénom, nom, sexe, date_naissance, statut, carrière, honorable]
        $simples = [
            ['Aline',   'Doko',     'F', '1998-03-10', 'actif',          'Formation',     true],
            ['Karim',   'Orou',     'M', '2000-07-22', 'actif',          'MC',            false],
            ['Chantal', 'Bagri',    'F', '1985-09-15', 'honoraire',      'Protocole',     true],
            ['Moussa',  'Tamou',    'M', '1979-01-18', 'past_president', 'Développement', false],
            ['Bernard', 'Koto',     'M', '1975-05-30', 'past_president', 'Développement', true],
            ['Estelle', 'Sagbo',    'F', '1990-09-27', 'membre_honneur', 'Communication', true],
            ['Ines',    'Fassinou', 'F', '2001-06-03', 'actif',          'Formation',     false],
            ['Prosper', 'Aholou',   'M', '1988-10-12', 'honoraire',      'MC',            true],
        ];
        foreach ($simples as [$prenom, $nom, $sexe, $dob, $statut, $carriere, $honorable]) {
            $membre = Membre::firstOrCreate(['nom' => $nom, 'prenom' => $prenom],
                ['sexe' => $sexe, 'date_naissance' => $dob, 'ville' => 'Djougou', 'fonction' => 'Membre', 'promotion' => 'Promotion Leaders '.rand(2021,2024),
                 'statut' => $statut, 'date_adhesion' => '2025-02-01',
                 'telephone' => '+229 01 '.rand(40,99).' '.rand(10,99).' '.rand(10,99).' '.rand(10,99),
                 'email' => $mail($prenom, $nom)]);
            Carriere::firstOrCreate(['membre_id' => $membre->id, 'type' => $carriere],
                ['annee' => (string) rand(2021, 2026), 'description' => 'Domaine : '.$carriere]);
            if ($honorable) {
                Cotisation::firstOrCreate(['membre_id' => $membre->id, 'mandat_id' => $mandat->id],
                    ['montant' => 15000, 'date_cotisation' => '2026-03-01']);
            }
            $user = User::firstOrCreate(['email' => $membre->email],
                ['name' => $membre->nom_complet, 'password' => 'password', 'membre_id' => $membre->id]);
            $user->syncRoles(['membre']);
        }

        /* ---- Postulants ---- */
        foreach ([
            ['Delphine','Assogba','F','nouveau'], ['Roland','Kpade','M','contacte'],
            ['Sylvie','Baba','F','en_formation'], ['Aristide','Gado','M','examen'],
            ['Rita','Sanni','F','admis'], ['Yves','Dossou','M','rejete'],
        ] as [$prenom, $nom, $sexe, $statut]) {
            Postulant::firstOrCreate(['nom' => $nom, 'prenom' => $prenom],
                ['sexe' => $sexe, 'ville' => 'Djougou', 'statut' => $statut,
                 'telephone' => '+229 01 '.rand(40,99).' '.rand(10,99).' '.rand(10,99).' '.rand(10,99),
                 'email' => $mail($prenom, $nom),
                 'motivation' => "Je souhaite m'engager pour le developpement de ma communaute.",
                 'date_naissance' => rand(1998,2004).'-0'.rand(1,9).'-1'.rand(0,9)]);
        }

        /* ---- Formations ---- */
        $vpf = Membre::where('fonction', 'VP Formations')->first();
        $fInt = Formateur::firstOrCreate(['nom' => 'Formateur interne (VPF)'],
            ['type' => 'interne', 'membre_id' => $vpf?->id, 'contact' => 'formation@jcidjougou.bj']);
        $fExt = Formateur::firstOrCreate(['nom' => 'Cabinet Élan Conseil'],
            ['type' => 'externe', 'contact' => 'contact@elanconseil.bj']);
        $f1 = Formation::firstOrCreate(['titre' => 'Introduction a la JCI'],
            ['theme' => 'Decouverte', 'objectifs' => 'Comprendre la mission, la vision et les valeurs.',
             'date_formation' => '2026-09-05 09:00', 'lieu' => 'Siege JCI Djougou', 'statut' => 'realisee',
             'mandat_id' => $mandat->id, 'formateur_id' => $fInt->id]);
        $f2 = Formation::firstOrCreate(['titre' => 'Techniques de prise de parole'],
            ['theme' => 'Leadership', 'objectifs' => "S'exprimer en public avec aisance.",
             'date_formation' => '2026-09-20 09:00', 'lieu' => 'Siege JCI Djougou', 'statut' => 'planifiee',
             'mandat_id' => $mandat->id, 'formateur_id' => $fExt->id]);
        foreach (Postulant::whereIn('statut', ['en_formation','admis'])->get() as $post) {
            Presence::firstOrCreate(['formation_id' => $f1->id, 'postulant_id' => $post->id], ['present' => true]);
            Presence::firstOrCreate(['formation_id' => $f2->id, 'postulant_id' => $post->id], ['present' => (bool) rand(0,1)]);
        }
        RapportFormation::firstOrCreate(['formation_id' => $f1->id],
            ['contenu' => "Séance réussie autour des fondamentaux de la JCI ; bonne participation des postulants."]);

        /* ---- Projets (avec responsables) ---- */
        $vpProjet = Membre::where('fonction', 'VP Projet & Thème principal')->first();
        $projetsRefs = [];
        foreach ([
            ['Reboisement scolaire', "Plantation d'arbres dans 5 ecoles de Djougou.", 'en_cours', 60],
            ['Don de sang citoyen', 'Campagne de collecte avec le centre de sante.', 'termine', 100],
            ['Alphabetisation numerique', 'Initiation a l informatique pour les jeunes.', 'a_venir', 10],
        ] as [$titre, $desc, $statut, $av]) {
            $projetsRefs[$titre] = Projet::firstOrCreate(['titre' => $titre],
                ['description' => $desc, 'statut' => $statut, 'avancement' => $av, 'public' => true,
                 'mandat_id' => $mandat->id, 'responsable_id' => $vpProjet?->id]);
        }

        /* ---- Partenaires ---- */
        $partRefs = [];
        foreach ([['Mairie de Djougou','Institution'], ['Radio Donga','Media'], ['MDE Informatique & Reseaux','Entreprise']] as [$nom, $type]) {
            $partRefs[$nom] = Partenaire::firstOrCreate(['nom' => $nom], ['type' => $type, 'public' => true]);
        }

        /* ---- Finances ---- */
        Contribution::firstOrCreate(['source' => 'Mairie de Djougou', 'projet_id' => $projetsRefs['Reboisement scolaire']->id],
            ['montant' => 200000, 'date_contribution' => '2026-04-10', 'partenaire_id' => $partRefs['Mairie de Djougou']->id]);
        Contribution::firstOrCreate(['source' => 'Radio Donga', 'projet_id' => null],
            ['montant' => 50000, 'date_contribution' => '2026-05-02', 'partenaire_id' => $partRefs['Radio Donga']->id]);
        Depense::firstOrCreate(['libelle' => 'Achat de plants', 'projet_id' => $projetsRefs['Reboisement scolaire']->id],
            ['categorie' => 'projet', 'montant' => 120000, 'date_depense' => '2026-04-20', 'mandat_id' => $mandat->id]);
        Depense::firstOrCreate(['libelle' => 'Fournitures de bureau', 'projet_id' => null],
            ['categorie' => 'secretariat', 'montant' => 30000, 'date_depense' => '2026-05-15', 'mandat_id' => $mandat->id]);
        Depense::firstOrCreate(['libelle' => 'Prestation graphiste (visuels)', 'projet_id' => null],
            ['categorie' => 'prestation', 'montant' => 45000, 'date_depense' => '2026-06-01', 'mandat_id' => $mandat->id]);
        Depense::firstOrCreate(['libelle' => 'Frais de secrétariat mensuel', 'projet_id' => null],
            ['categorie' => 'fonctionnement', 'montant' => 20000, 'date_depense' => '2026-06-10', 'mandat_id' => $mandat->id]);

        /* ---- Archives / Documents ---- */
        foreach ([
            ['Rapport de formation - Introduction JCI', 'rapport', '2026-09-06'],
            ['PV réunion du bureau - Août', 'document', '2026-08-30'],
            ['Photos - Don de sang citoyen', 'photo', '2026-07-15'],
        ] as [$titre, $type, $date]) {
            Archive::firstOrCreate(['titre' => $titre],
                ['type' => $type, 'date_document' => $date, 'mandat_id' => $mandat->id,
                 'description' => 'Document archivé par le Secrétariat Général.']);
        }

        /* ---- Standards d'efficacité (100%) ---- */
        foreach ([
            ['Tenue régulière des réunions du bureau', 'Gestion', true],
            ['Plan d\'action validé en début de mandat', 'Gestion', true],
            ['Au moins 3 projets réalisés', 'Projets', false],
            ['Rapport financier à jour', 'Finances', true],
            ['Recrutement de nouveaux membres', 'Croissance', false],
            ['Présence aux événements nationaux', 'Communication', false],
        ] as [$libelle, $cat, $atteint]) {
            Standard::firstOrCreate(['libelle' => $libelle],
                ['categorie' => $cat, 'atteint' => $atteint, 'mandat_id' => $mandat->id]);
        }

        /* ---- Plan d'action ---- */
        foreach ([
            ['Lancer la campagne de reboisement', 'en_cours', '2026-09'],
            ['Former les nouveaux postulants', 'en_cours', '2026-09'],
            ['Assemblée générale de mi-mandat', 'a_faire', '2026-10'],
            ['Bilan financier trimestriel', 'a_faire', '2026-10'],
        ] as [$titre, $statut, $mois]) {
            PlanAction::firstOrCreate(['titre' => $titre],
                ['statut' => $statut, 'mois' => $mois, 'mandat_id' => $mandat->id]);
        }
    }
}
