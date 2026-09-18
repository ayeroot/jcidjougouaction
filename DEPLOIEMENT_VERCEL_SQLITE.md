# Déploiement Vercel avec SQLite (démo / aperçu)

> ⚠️ **À lire.** Ce mode sert à **mettre le site en ligne rapidement pour le montrer**.
> Sur Vercel, le disque est **éphémère** : la base SQLite est copiée dans `/tmp` au démarrage,
> donc **toute donnée créée en ligne (comptes, membres, inscriptions…) est perdue** quand
> l'instance est recyclée, et n'est pas partagée entre deux visiteurs simultanés.
> **Les photos téléversées ne fonctionnent pas non plus** (disque en lecture seule).
> Pour un vrai usage, brancher une base MySQL/Postgres distante (voir `DEPLOIEMENT_VERCEL.md`)
> ou héberger sur LWS.

Le projet est **déjà préparé** pour ce mode : `api/index.php`, `vercel.json`, `config/view.php`
et une base pré-remplie `database/base_demo.db` (avec l'admin, les rôles et les permissions).

---

## 1. Générer la clé d'application (en local)

```bash
php artisan key:generate --show
```
Copie la valeur `base64:...` (tu la colleras dans `APP_KEY` sur Vercel).

## 2. Pousser le projet sur GitHub

Les fichiers Vercel sont déjà là. La base `database/base_demo.db` a l'extension `.db`
(**non ignorée par git**), donc elle sera bien poussée :
```bash
git add .
git commit -m "Déploiement Vercel (SQLite démo)"
git push
```

## 3. Importer le projet sur Vercel

1. **vercel.com** → **Add New… → Project** → importe ton dépôt GitHub.
2. **Framework preset : « Other »** (le `vercel.json` gère tout).
3. Aucune build command à définir (Tailwind est en CDN, pas de build npm).

## 4. Variables d'environnement Vercel

**Settings → Environment Variables** (environnement **Production**) :

```
APP_NAME            = JCI Djougou Action
APP_ENV             = production
APP_KEY             = base64:...   ← valeur de l'étape 1
APP_DEBUG           = false
APP_URL             = https://ton-projet.vercel.app

DB_CONNECTION       = sqlite
DB_DATABASE         = /tmp/database.sqlite

VIEW_COMPILED_PATH  = /tmp/views
SESSION_DRIVER      = cookie
CACHE_STORE         = array
QUEUE_CONNECTION    = sync
LOG_CHANNEL         = stderr
MAIL_MAILER         = log
```

> `SESSION_DRIVER=cookie` évite d'écrire les sessions sur le disque (impossible sur Vercel).
> `LOG_CHANNEL=stderr` envoie les logs dans les **Runtime Logs** de Vercel.

## 5. Déployer

Clique **Deploy** (ou `git push`). Une fois **Ready**, ouvre l'URL :

- `/` → la vitrine s'affiche.
- `/connexion` → connexion avec **`admin@jcidjougou.bj` / `password`**.

## 6. Vérifier

- La vitrine et le tableau de bord s'affichent.
- La connexion fonctionne (sessions par cookie).

Si tu vois une erreur **« Please provide a valid cache path »** → la variable
`VIEW_COMPILED_PATH=/tmp/views` n'est pas définie (étape 4).
Si tu vois **500** → active temporairement `APP_DEBUG=true`, recharge, lis le message,
puis remets `false`.

---

## Rappel des limites de ce mode

| | SQLite démo (Vercel) |
|---|---|
| Voir le site en ligne | ✅ |
| Se connecter, naviguer | ✅ |
| **Conserver les données créées** | ❌ (réinitialisées) |
| **Téléverser des photos** | ❌ |

➡️ Quand tu veux un site **réel avec données persistantes** : branche une base distante
(MySQL TiDB/Aiven/Clever Cloud, ou Postgres Neon) — voir `DEPLOIEMENT_VERCEL.md` — ou héberge
sur **LWS**. Il suffira alors de changer les variables `DB_*` (aucun autre changement de code).
