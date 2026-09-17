# Déploiement sur Vercel avec MySQL

> **À lire d'abord.** Vercel est une plateforme *serverless* pensée pour Node/front-end.
> Laravel peut y tourner via un *runtime PHP communautaire*, mais avec des contraintes :
> le système de fichiers est **éphémère** (aucun fichier uploadé n'est conservé) et il n'y
> a **pas de serveur permanent**. Il faut donc :
> - une **base MySQL externe** (PlanetScale, Railway, Aiven, Clever Cloud… ),
> - un **stockage d'objets S3** pour les photos/logos (AWS S3, Cloudflare R2),
> - les **sessions et le cache en base de données** (pas de fichiers).
>
> Pour une mise en ligne plus simple d'un projet Laravel, **Railway**, **Render** ou un
> hébergement mutualisé cPanel (type LWS) exécutent Laravel nativement sans ces contraintes.
> Le guide Vercel ci-dessous reste fourni tel que demandé.

---

## 1. Base de données MySQL (externe)

Créez une base MySQL chez un fournisseur managé, puis notez les identifiants
(host, port, database, username, password). Exemple de variables :

```
DB_CONNECTION=mysql
DB_HOST=xxxxx.provider.com
DB_PORT=3306
DB_DATABASE=jci_djougou
DB_USERNAME=xxxx
DB_PASSWORD=xxxx
```

## 2. Stockage des fichiers (S3 / R2)

Les uploads (photos des membres, logo et photo de famille du mandat) **ne persistent pas**
sur Vercel : configurez un disque S3.

```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=...
AWS_BUCKET=...
AWS_URL=https://votre-bucket.s3.amazonaws.com
```

Installez le pilote : `composer require league/flysystem-aws-s3-v3`.

## 3. Sessions & cache en base

```
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Les tables `sessions`, `cache` et `jobs` sont créées par les migrations fournies.

## 4. Fichiers de configuration Vercel

À la racine du projet, créez **`vercel.json`** :

```json
{
  "version": 2,
  "framework": null,
  "functions": { "api/index.php": { "runtime": "vercel-php@0.7.3" } },
  "routes": [
    { "src": "/(.*)", "dest": "/api/index.php" }
  ]
}
```

Et **`api/index.php`** (point d'entrée qui charge le `public/index.php` de Laravel) :

```php
<?php
require __DIR__ . '/../public/index.php';
```

> `vercel-php` est le runtime PHP communautaire pour Vercel. Vérifiez la dernière version
> disponible et adaptez le numéro (`vercel-php@x.y.z`).

## 5. Variables d'environnement sur Vercel

Dans **Project Settings → Environment Variables**, ajoutez au minimum :

```
APP_NAME="JCI Djougou Action"
APP_ENV=production
APP_KEY=base64:...        # généré via: php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://votre-projet.vercel.app
DB_CONNECTION=mysql + les DB_* ci-dessus
SESSION_DRIVER=database
CACHE_STORE=database
FILESYSTEM_DISK=s3 + les AWS_* ci-dessus
```

## 6. Migrations

Les migrations ne s'exécutent pas automatiquement sur Vercel. Depuis votre poste,
pointez le `.env` sur la base MySQL de production et lancez une seule fois :

```bash
php artisan migrate --force
php artisan db:seed --force     # (facultatif : données de démonstration)
```

## 7. Déploiement

```bash
npm i -g vercel
vercel        # prévisualisation
vercel --prod # production
```

---

## Checklist de sécurité (avant mise en production)

- [ ] `APP_DEBUG=false` et `APP_ENV=production` (ne jamais exposer les traces d'erreur).
- [ ] `APP_KEY` généré et gardé secret ; `.env` **non** versionné (déjà dans `.gitignore`).
- [ ] **HTTPS** forcé (Vercel fournit le TLS automatiquement).
- [ ] Identifiants MySQL forts ; accès base restreint aux IP nécessaires.
- [ ] Mots de passe des comptes de démonstration **changés** (le seed utilise `password`).
- [ ] Uploads limités aux images et à 2–4 Mo (déjà validé côté serveur).
- [ ] Connexion protégée contre la force brute (déjà : `throttle:6,1` sur `/connexion`).
- [ ] Rôles vérifiés : le Président consulte tout mais ne modifie pas les finances ;
      chaque VP n'accède qu'à son module (contrôlé par middleware `role:`).
- [ ] Protection CSRF active sur tous les formulaires (native Laravel).
- [ ] Sauvegardes régulières de la base MySQL.

