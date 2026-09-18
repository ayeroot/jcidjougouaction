<?php
namespace App\Support;

/**
 * Catalogue des permissions de l'application.
 *
 * Principe :
 *   - Le CODE définit les permissions disponibles (ci-dessous).
 *   - Le DASHBOARD permet à l'administrateur d'attribuer ces permissions
 *     aux rôles et aux utilisateurs, sans modification du code.
 *
 * Ces permissions sont enregistrées dans les tables spatie/laravel-permission
 * (mêmes tables que les rôles), et vérifiées via le middleware `permission:` et
 * les directives Blade `@can`. Aucun système parallèle n'est introduit.
 */
class Permissions
{
    /** slug => [libellé, groupe] */
    public const CATALOGUE = [
        'membres.gerer'      => ['Gérer les membres',            'Membres'],
        'postulants.voir'    => ['Consulter le recrutement',     'Recrutement'],
        'postulants.gerer'   => ['Gérer le recrutement',         'Recrutement'],
        'formations.voir'    => ['Consulter les formations',     'Formations'],
        'formations.gerer'   => ['Gérer les formations',         'Formations'],
        'finances.voir'      => ['Consulter les finances',       'Finances'],
        'finances.gerer'     => ['Gérer les finances',           'Finances'],
        'projets.gerer'      => ['Gérer les projets',            'Projets'],
        'partenaires.voir'   => ['Consulter les partenaires',    'Partenaires'],
        'partenaires.gerer'  => ['Gérer les partenaires',        'Partenaires'],
        'archives.voir'      => ['Consulter les archives',       'Archives'],
        'archives.gerer'     => ['Gérer les archives',           'Archives'],
        'efficacite.voir'    => ['Consulter 100% efficacité',    '100% efficacité'],
        'efficacite.gerer'   => ['Gérer 100% efficacité',        '100% efficacité'],
        'mandats.voir'       => ['Consulter les mandats',        'Mandats'],
        'mandats.gerer'      => ['Configurer les mandats / CDL',  'Mandats'],
        'historique.voir'    => ["Consulter l'historique",       'Administration'],
        'vitrine.gerer'      => ['Gérer le site vitrine',        'Administration'],
        'utilisateurs.gerer' => ['Gérer les comptes & privilèges', 'Administration'],
    ];

    /**
     * Permissions par défaut de chaque rôle (utilisées au seeding et pour
     * réinitialiser un rôle). Reproduit exactement les accès actuels.
     */
    public const DEFAUTS_ROLES = [
        'admin' => [
            // Accès en LECTURE à tous les modules SAUF Finance, + administration.
            'postulants.voir', 'formations.voir', 'partenaires.voir', 'archives.voir',
            'efficacite.voir', 'mandats.voir', 'historique.voir',
            'utilisateurs.gerer', 'vitrine.gerer',
        ],
        'president' => [
            // Tout sauf l'écriture des finances et la gestion des comptes.
            'membres.gerer',
            'postulants.voir', 'postulants.gerer',
            'formations.voir', 'formations.gerer',
            'finances.voir',
            'projets.gerer',
            'partenaires.voir', 'partenaires.gerer',
            'archives.voir', 'archives.gerer',
            'efficacite.voir', 'efficacite.gerer',
            'mandats.voir', 'mandats.gerer',
            'historique.voir', 'vitrine.gerer',
        ],
        'vpm'        => ['membres.gerer'],
        'vpcd'       => ['postulants.voir', 'postulants.gerer'],
        'vpf'        => ['postulants.voir', 'formations.voir', 'formations.gerer'],
        'tresorier'  => ['finances.voir', 'finances.gerer'],
        'vpre'       => ['partenaires.voir', 'partenaires.gerer'],
        'secretaire' => ['archives.voir', 'archives.gerer'],
        'vpe'        => ['efficacite.voir', 'efficacite.gerer', 'historique.voir'],
        'vp_projet'  => ['projets.gerer'],
        'membre'     => [],
    ];

    /** Libellés lisibles des rôles. */
    public const ROLES = [
        'admin'      => 'Administrateur',
        'president'  => 'Président Local',
        'vpe'        => 'VP Exécutive',
        'vpre'       => 'VP Relations Extérieures',
        'vpf'        => 'VP Formations',
        'vpm'        => 'VP Management',
        'vpcd'       => 'VP Croissance & Développement',
        'vp_projet'  => 'VP Projet & Thème',
        'tresorier'  => 'Trésorier Général',
        'secretaire' => 'Secrétaire Général',
        'membre'     => 'Membre',
    ];

    public static function slugs(): array
    {
        return array_keys(self::CATALOGUE);
    }

    public static function libelle(string $slug): string
    {
        return self::CATALOGUE[$slug][0] ?? $slug;
    }

    /** Catalogue groupé par module : [groupe => [slug => libellé]]. */
    public static function parGroupe(): array
    {
        $groupes = [];
        foreach (self::CATALOGUE as $slug => [$libelle, $groupe]) {
            $groupes[$groupe][$slug] = $libelle;
        }
        return $groupes;
    }
}
