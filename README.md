# Plateforme JCI Djougou Action

Application web de gestion pour l'Organisation Locale Membre **JCI Djougou Action** :
site vitrine public + espace de gestion privé (tableau de bord) avec gestion des rôles.

- **Backend :** Laravel 13 (PHP 8.3+)
- **Interface :** Blade + Tailwind CSS
- **Base de données :** SQLite (développement) — MySQL en production
- **Rôles & permissions :** spatie/laravel-permission
- **Traçabilité :** journal d'audit maison (trait `App\Support\Auditable`)

---

## 1. Prérequis

- PHP **8.3+** avec `pdo_sqlite`, `mbstring`, `xml`, `curl`
- [Composer](https://getcomposer.org)

## 2. Installation (en local)

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Ouvrez **http://127.0.0.1:8000** (vitrine `/`, inscription `/inscription`, connexion `/connexion`).

## 3. Comptes de démonstration

Les comptes du CDL sont identifiés par la **fonction** (pas de nom de personne).
Mot de passe pour tous : `password`

| Fonction | Identifiant (email) |
|----------|---------------------|
| Président (accès complet) | `president.local@jcidjougou.bj` |
| VP Exécutive | `vpe.local@jcidjougou.bj` |
| VP Relations Extérieures | `vpre.local@jcidjougou.bj` |
| VP Formations (module Formations) | `vpf.local@jcidjougou.bj` |
| VP Management (module Membres) | `vpm.local@jcidjougou.bj` |
| VP Croissance (module Recrutement) | `vpcd.local@jcidjougou.bj` |
| VP Projet & Thème | `vp-projet.local@jcidjougou.bj` |
| Trésorier (module Finances) | `tresorier.local@jcidjougou.bj` |
| Secrétaire Général | `secretaire.local@jcidjougou.bj` |
| Membre simple (accès restreint) | `karim.orou@example.bj` |

> Astuce : le lien **Recrutement** n'apparaît que pour le VPCD, le VPF et le Président ;
> **Formations** pour le VPF et le Président ; **Finances** pour le Trésorier et le Président.

## 4. Modules disponibles

- **Fondations :** Laravel + SQLite + Tailwind, rôles/permissions, journal d'audit, entité *Mandat*.
- **Vitrine :** accueil, projets, partenaires, formulaire d'inscription des postulants.
- **Authentification** et espace privé protégé par rôle.
- **Membres :** liste (visible par tous), filtres, fiche avec **visibilité par rôle**,
  fonction en **liste déroulante**, statut **honorable calculé**, CRUD réservé VPM/Président.
- **Recrutement :** pipeline des postulants, changement de statut, **conversion en membre**,
  relevé des formations, relevé individuel imprimable.
- **Formations :** planification, affectation d'un formateur (interne/externe),
  **liste de présence imprimable**, **pointage** des présents, **rapport** (présents injectés
  automatiquement), filtre « formations du mois ».
- **Finances :** cotisations, contributions, dépenses, **solde de caisse**, budget par projet,
  suivi des membres honorables.

### Prochains lots

Projets (suivi détaillé), Partenaires, Archives, Plan d'action & Efficacité 100 %,
export PDF natif (dompdf), sauvegardes automatiques.

## 5. Passage en production

1. **Tailwind :** CDN en développement ; en production, `npm install` + `npm run build`
   puis remplacer le `<script src="cdn.tailwindcss.com">` des gabarits par `@vite(['resources/css/app.css'])`.
2. **Base de données :** basculer SQLite → **MySQL** dans `.env` (DB_CONNECTION=mysql, DB_DATABASE, …),
   puis `php artisan migrate --seed`.

## 6. Organisation du code

```
app/Http/Controllers/   Public/, Auth/, Dashboard, Membre, Postulant, Formation, Finance
app/Models/             Mandat, Membre, Postulant, Formation, Presence, Cotisation, ... (14 modeles)
app/Support/Auditable.php   Trait de journalisation (audit)
database/migrations/    Schema complet des 12 modules
database/seeders/       Donnees de demonstration
resources/views/        layouts/, public/, auth/, membres/, postulants/, formations/, finances/
routes/web.php          Public, auth, espace prive (protege par role)
```

---

(c) JCI Djougou Action.
