# Plateforme JCI Djougou Action

Application web de gestion pour l'Organisation Locale Membre **JCI Djougou Action** :
site vitrine public + espace de gestion privé avec gestion des rôles. Thème **bleu aqua**.

- **Backend :** Laravel 13 (PHP 8.3+) · **Interface :** Blade + Tailwind CSS
- **Base de données :** SQLite (développement) — MySQL en production
- **Rôles :** spatie/laravel-permission · **Traçabilité :** journal d'audit (trait `Auditable`)

---

## Installation (en local)

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link      # indispensable pour afficher les photos/logos téléversés
php artisan serve
```

Ouvrez **http://127.0.0.1:8000**.

## Comptes de démonstration

Comptes du CDL identifiés par la **fonction**. Mot de passe : `password`
*(à changer avant toute mise en production).*

| Fonction | Identifiant |
|----------|-------------|
| **Administrateur** (crée les comptes, attribue les rôles) | `admin@jcidjougou.bj` |
| Président (voit tout, **ne modifie pas** les finances) | `president.local@jcidjougou.bj` |
| VP Exécutive (Efficacité, Historique) | `vpe.local@jcidjougou.bj` |
| VP Relations Ext. (Partenaires) | `vpre.local@jcidjougou.bj` |
| VP Formations | `vpf.local@jcidjougou.bj` |
| VP Management (Membres) | `vpm.local@jcidjougou.bj` |
| VP Croissance (Recrutement) | `vpcd.local@jcidjougou.bj` |
| VP Projet | `vp-projet.local@jcidjougou.bj` |
| Trésorier (Finances) | `tresorier.local@jcidjougou.bj` |
| Secrétaire Général (Archives) | `secretaire.local@jcidjougou.bj` |
| Membre simple | `karim.orou@example.bj` |

## Modules

- **Administration des comptes** (réservé à l'administrateur) : l'admin **crée les identifiants**
  et **attribue les rôles** ; un mot de passe provisoire est généré et **envoyé par email** au nouvel
  utilisateur avec le lien de connexion. Réinitialisation du mot de passe (avec renvoi d'email) et
  suppression de compte. En développement, les emails sont écrits dans `storage/logs/laravel.log`
  (`MAIL_MAILER=log`) ; en production, configurer un vrai SMTP dans le `.env`.
- **Vitrine + inscription** des postulants.
- **Membres** : liste, filtres (recherche, statut, carrière, moins de 40 ans),
  fiche avec **photo** et **nom de promotion**, visibilité par rôle, fonction en liste déroulante,
  statut *honorable* calculé (cotisation entièrement réglée).
- **Anniversaires** : membres fêtés, par mois.
- **Recrutement** : pipeline *nouveau → contacté → en formation → examen → intégré* ;
  intégration en membre **avec nom de promotion** (après formations et examen réussi) ; relevé imprimable.
- **Formations** : planification, formateur, liste de présence imprimable, pointage,
  rapport (présents injectés), filtre « formations du mois ».
- **Finances** : cotisations en **paiement échelonné** (tranches, reste à payer),
  contributions, **dépenses par catégorie** (projet, prestation graphiste, secrétariat,
  fonctionnement…) y compris **hors projet**, solde, budget par projet.
  *Le Président consulte mais ne modifie pas ; seul le Trésorier enregistre les mouvements.*
- **Projets**, **Partenaires**, **Archives** (par type + référence/lien).
- **Plan d'action & Efficacité 100%** : standards suivis (% d'efficacité) + plan d'action mensuel.
- **Mandats** : configuration par mandat (**logo, thème, couleur, photo de famille du CDL,
  liste du CDL**) et **historique des mandats**.
- **Historique des activités** : journal d'audit (qui a fait quoi, quand).

## Sécurité

- Rôles et permissions par middleware ; le Président ne modifie pas les finances.
- Connexion protégée contre la force brute (`throttle:6,1`).
- CSRF natif, validation stricte, uploads limités aux images (2–4 Mo), mots de passe hachés.
- **Avant production** : `APP_DEBUG=false`, HTTPS, changer les mots de passe de démonstration,
  basculer sur MySQL. Voir la checklist dans `DEPLOIEMENT_VERCEL.md`.

## Déploiement

- **Vercel + MySQL** : voir **`DEPLOIEMENT_VERCEL.md`** (avec ses contraintes serverless).
- **Production classique** : passer `DB_CONNECTION=mysql` dans `.env`, compiler Tailwind
  (`npm install && npm run build`, puis remplacer le CDN par `@vite`), et lancer `php artisan migrate --seed`.

---

(c) JCI Djougou Action.
