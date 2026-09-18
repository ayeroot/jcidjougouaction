# Déploiement — Vercel (application) + LWS (base de données MySQL)

Ce guide décrit le déploiement de la plateforme **JCI Djougou Action** avec :

- **Application Laravel 13** hébergée sur **Vercel** (via le runtime PHP communautaire) ;
- **Base de données MySQL** hébergée chez **LWS**.

> ### ⚠️ À lire avant de commencer — contrainte importante
> Vercel est une plateforme **serverless** (sans serveur permanent, système de fichiers éphémère).
> Deux points d'attention majeurs avec cette stack :
>
> 1. **Accès distant à la base LWS.** Le MySQL d'un hébergement **mutualisé LWS** n'accepte, par
>    défaut, que les connexions **depuis les serveurs LWS**. Vercel se connecte depuis des IP
>    **dynamiques** : il faut donc **activer l'accès distant** et autoriser l'hôte `%` (toutes IP)
>    pour l'utilisateur MySQL. Si votre offre LWS **n'autorise pas** l'accès MySQL distant (fréquent
>    en mutualisé), ce montage **ne fonctionnera pas** : voir l'alternative en fin de document.
> 2. **Fichiers téléversés** (photos des membres, logos) : le disque Vercel est éphémère.
>    Il faut un stockage externe **S3 / Cloudflare R2** (les fichiers ne persistent pas sinon).
>
> Pour un déploiement **plus simple et sans ces contraintes**, héberger l'application **directement
> sur un hébergement LWS** (mutualisé compatible PHP 8.3+, ou VPS) est recommandé — voir la section
> « Alternative » à la fin.

---

## 1. Préparation du projet

Dans le projet, à la racine, créez **`api/index.php`** (point d'entrée serverless) :

```php
<?php
require __DIR__ . '/../public/index.php';
```

Et **`vercel.json`** :

```json
{
  "version": 2,
  "functions": {
    "api/index.php": { "runtime": "vercel-php@0.7.3" }
  },
  "routes": [
    { "src": "/(.*)", "dest": "/api/index.php" }
  ]
}
```

> Vérifiez la dernière version du runtime `vercel-php` et adaptez le numéro.

Vérifiez en local que tout fonctionne :

```bash
composer install
php artisan test        # doit passer
```

Poussez le projet sur GitHub (branche `main`).

## 2. Configuration de la base de données LWS

1. Connectez-vous à votre **espace client LWS** → **Hébergement** → **Bases de données MySQL**.
2. **Créez une base** (ex. `jci_djougou`) et un **utilisateur** MySQL avec un mot de passe fort.
3. Notez : **hôte** (ex. `xxxxxx.mysql.db` ou une IP), **port** (`3306`), **nom de la base**,
   **utilisateur**, **mot de passe**.
4. **Activez l'accès distant** : rubrique **« Accès distant »** / **« Remote MySQL »** de la base,
   et autorisez l'hôte **`%`** (toutes les IP) — indispensable car les IP de Vercel sont dynamiques.
   *(Si l'option est absente ou refusée, voir l'alternative en fin de document.)*

## 3. Variables d'environnement (rappel des clés utilisées)

L'application lit ces variables (voir `.env.example`) :

```dotenv
APP_NAME="JCI Djougou Action"
APP_ENV=production
APP_KEY=base64:...            # généré (voir étape 4)
APP_DEBUG=false
APP_URL=https://votre-projet.vercel.app

DB_CONNECTION=mysql
DB_HOST=xxxxxx.mysql.db       # hôte MySQL LWS
DB_PORT=3306
DB_DATABASE=jci_djougou
DB_USERNAME=votre_user_lws
DB_PASSWORD=********

SESSION_DRIVER=database        # sessions en base (pas de fichiers sur Vercel)
CACHE_STORE=database
QUEUE_CONNECTION=database

# Emails (voir SMTP_SETUP.md) — ex. SMTP LWS ou un service transactionnel
MAIL_MAILER=smtp
MAIL_HOST=smtp.votre-domaine
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=no-reply@jcidjougou.org
MAIL_PASSWORD=********
MAIL_FROM_ADDRESS="no-reply@jcidjougou.org"
MAIL_FROM_NAME="JCI Djougou Action"

# Stockage des fichiers téléversés (obligatoire sur Vercel — disque éphémère)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=...
AWS_BUCKET=...
AWS_URL=https://votre-bucket.s3.amazonaws.com
```

Installez le pilote S3 si vous utilisez le stockage externe :

```bash
composer require league/flysystem-aws-s3-v3
```

## 4. Migration de la base de données

Les migrations **ne s'exécutent pas** automatiquement sur Vercel. Depuis votre poste, pointez le
`.env` **sur la base MySQL LWS** (les valeurs `DB_*` ci-dessus) puis exécutez **une seule fois** :

```bash
php artisan key:generate --show     # copiez la valeur pour APP_KEY (étape 7)
php artisan migrate --force
php artisan db:seed --force         # crée le compte administrateur + le catalogue de permissions
```

> Le seed crée l'administrateur `admin@jcidjougou.bj` (mot de passe `password`) : **changez-le
> immédiatement** après la première connexion (voir étape 11).

## 5. Configuration du projet Vercel

1. Sur **vercel.com**, cliquez **« Add New… » → « Project »**.
2. **Importez** votre dépôt GitHub.
3. **Framework preset** : « Other » (le `vercel.json` gère la configuration).
4. Ne définissez pas de « build command » spécifique (le runtime PHP s'en charge).

## 6. Connexion du repository

Une fois le dépôt importé, Vercel redéploie automatiquement à chaque `git push` sur `main`.

## 7. Variables d'environnement Vercel

Dans **Project Settings → Environment Variables**, ajoutez **toutes** les variables de l'étape 3
(APP_KEY généré à l'étape 4, DB_*, MAIL_*, AWS_* le cas échéant), pour l'environnement
**Production**. Puis redéployez.

## 8. Déploiement

```bash
npm i -g vercel
vercel          # déploiement de prévisualisation
vercel --prod   # déploiement en production
```

Ou simplement `git push` (Vercel déploie automatiquement).

## 9. Vérification du build

- Ouvrez l'onglet **Deployments** de Vercel → le dernier build doit être **Ready**.
- En cas d'erreur, consultez les **Build Logs** et les **Runtime Logs**.

## 10. Vérification de la connexion à la base LWS

- Ouvrez `https://votre-projet.vercel.app/` : la page d'accueil doit s'afficher (elle lit la base).
- Une erreur `SQLSTATE[HY000] Connection refused` ou timeout = accès distant MySQL non autorisé
  (revoir l'étape 2, point 4) ou identifiants `DB_*` incorrects.

## 11. Configuration du premier administrateur

1. Allez sur `https://votre-projet.vercel.app/connexion`.
2. Connectez-vous avec `admin@jcidjougou.bj` / `password`.
3. Ouvrez **Mon profil** (en haut à droite) → **Changer mon mot de passe** et définissez un mot
   de passe fort.
4. (Optionnel) Créez d'autres comptes depuis **Administration → Utilisateurs**.

## 12. Vérification de l'authentification

- Connexion / déconnexion.
- « Mot de passe oublié » envoie bien un email (nécessite le SMTP de l'étape 3).
- Un compte **suspendu** ne peut pas se connecter.

## 13. Vérification des rôles

- **Administration → Rôles & permissions** : la liste des 11 rôles et leurs permissions s'affiche.
- Modifier les permissions d'un rôle impacte tous ses utilisateurs.

## 14. Vérification des permissions

- Ouvrir un utilisateur (**Administration → Utilisateurs → Gérer**), accorder une permission
  supplémentaire, puis vérifier que l'utilisateur y a accès (et l'inverse en la retirant).
- Vérifier qu'un utilisateur **ne peut pas** accéder à une route dont il n'a pas la permission
  (redirection / page 403).

## 15. Vérification de la gestion des utilisateurs

- Recherche, filtres (rôle, état), création de compte à partir d'un membre, activation par email,
  suspension / réactivation.

## 16. Vérification de la gestion du site vitrine

- **Administration → Site vitrine** : modifier le titre, la mission, le contact, activer/désactiver
  une section, puis recharger `/` pour voir le changement.

## 17. Vérification des pages d'erreur

- Ouvrir une URL inexistante → page **404** personnalisée.
- Accéder à une route sans permission → page **403** personnalisée.

---

## Alternative recommandée : hébergement LWS direct

Laravel tourne **nativement** sur un hébergement **mutualisé LWS** compatible PHP 8.3+ (ou un VPS
LWS), **sans** les contraintes serverless de Vercel (base locale, fichiers persistants) :

1. Créez la base MySQL LWS (étape 2, l'accès distant n'est alors **pas** nécessaire : l'app et la
   base sont sur le même hébergeur).
2. Téléversez le projet (Git ou FTP) dans le dossier de votre domaine.
3. Faites pointer le domaine sur le dossier **`public/`** du projet.
4. Créez le `.env` (avec les `DB_*` LWS, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`).
5. En SSH : `composer install --no-dev`, `php artisan key:generate`,
   `php artisan migrate --seed --force`, `php artisan storage:link`,
   `php artisan config:cache`.
6. Compilez les assets en local (`npm install && npm run build`) et téléversez `public/build`.

Ce montage est **plus simple, plus fiable et moins coûteux** pour une OLM locale, et évite le
stockage S3 externe.

---

## Récapitulatif des variables d'environnement

| Variable | Rôle |
|----------|------|
| `APP_KEY` | Clé d'application (obligatoire, générée) |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` en production |
| `APP_URL` | URL publique du site |
| `DB_*` | Connexion MySQL LWS |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | `database` (indispensable sur Vercel) |
| `MAIL_*` | Envoi d'emails (voir `SMTP_SETUP.md`) |
| `FILESYSTEM_DISK`, `AWS_*` | Stockage des fichiers (Vercel uniquement) |
