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

> ⚠️ **`php artisan storage:link` est indispensable** pour que les photos et logos
> téléversés s'affichent (crée le lien `public/storage`). Sans lui, les images renvoient une 404.

### Configuration des e-mails

Par défaut `MAIL_MAILER=log` : les e-mails (activation de compte, réinitialisation) sont
écrits dans `storage/logs/laravel.log` — aucune configuration requise en développement.
Pour un envoi réel, voir **`SMTP_SETUP.md`**.

## Comptes de démonstration

Comptes du CDL identifiés par la **fonction**. Mot de passe : `password`
*(à changer avant toute mise en production).*

| Fonction | Identifiant |
|----------|-------------|
| **Administrateur** (crée les comptes, READ partout **sauf Finance**) | `admin@jcidjougou.bj` |
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

- **Administration des comptes** (réservé à l'administrateur) : l'admin **sélectionne un membre
  existant** (aucune ressaisie des informations) et **lui attribue un rôle** ; un **email
  d'activation** est envoyé au membre pour qu'il **définisse lui-même son mot de passe**.
  Renvoi d'activation, réinitialisation du mot de passe, activation/désactivation et suppression
  de compte. L'admin peut modifier l'email d'un compte (l'utilisateur, lui, ne le peut pas).
- **Activation & mot de passe** : lien d'activation à durée limitée ; **mot de passe oublié**
  avec token sécurisé et expiration ; **profil personnel** (chacun modifie ses infos, mais **pas
  son email** — réservé à l'admin, contrôle backend).
- **Vitrine + inscription** des postulants.
- **Membres** : liste, filtres (recherche, statut, carrière, moins de 40 ans),
  fiche avec **photo obligatoire à la création**, nom de promotion, visibilité par rôle,
  fonction en liste déroulante, statut *honorable* calculé.
- **Anniversaires** : membres fêtés, par mois.
- **Recrutement** : pipeline *nouveau → contacté → en formation → examen → intégré*.
- **Formations** : **liste vide au départ** (saisie manuelle) ; planification, formateur,
  liste de présence imprimable (**signature du formateur en bas**), pointage,
  **rapport soumis uniquement après la fin** de la formation ; statuts
  *Planifiée → En cours → Terminée → Rapport soumis*.
- **Finances** : cotisations en **paiement échelonné**, contributions,
  **dépenses par catégorie** y compris **hors projet**, solde, budget par projet.
  *Trésorier + Président uniquement ; l'administrateur n'y a **pas** accès.*
- **Projets**, **Partenaires**, **Archives** (par type + référence/lien).
- **100% efficacité** : standards suivis (% d'efficacité) + plan d'action mensuel.
- **Mandats** : configuration par mandat (**logo, thème, couleur, photo de famille du CDL**) ;
  **configuration du CDL** : affecter un membre à chaque poste → **création automatique de son
  compte** avec le rôle correspondant, **un seul membre par poste et par année**,
  **désactivation automatique** de l'ancien titulaire.
- **Historique des activités** : audit **backend** de toutes les actions CREATE/UPDATE/DELETE
  (jamais les lectures), avec auteur, **rôle**, date/heure, action, entité, id, et **anciennes /
  nouvelles valeurs** pour les modifications.

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
