# Déploiement sur VPS — JCI Djougou Action

Systèmes pris en charge : **Ubuntu 24.04 / 22.04** ou **Debian 12**, VPS neuf.
Taille conseillée : 1 vCPU, 1 à 2 Go de RAM, 20 Go de disque.

## Avant de commencer

1. **DNS** — chez ton registrar, crée deux enregistrements A vers l'IP du VPS :
   `ton-domaine` et `www.ton-domaine`. (Le script marche aussi sans : il obtiendra
   le certificat HTTPS quand tu le relanceras, une fois le DNS propagé.)
2. **Clé SSH** — depuis ton Mac, une seule fois :
   ```bash
   ssh-copy-id root@IP_DU_VPS
   ```
   Sans clé, le script n'interdit pas les mots de passe SSH (pour ne pas t'enfermer dehors).
3. **Dépôt privé ?** — le script affichera une clé à ajouter dans GitHub →
   dépôt → *Settings → Deploy keys* (lecture seule). Il faudra alors le relancer avec
   `DEPOT=git@github.com:ayeroot/jcidjougouaction.git`.

## 1. Préparer le serveur (une fois, en root)

```bash
ssh root@IP_DU_VPS
curl -fsSLO https://raw.githubusercontent.com/ayeroot/jcidjougouaction/main/deploiement/preparer-vps.sh
bash preparer-vps.sh
```
(Dépôt privé : copie le fichier depuis ton Mac avec
`scp deploiement/preparer-vps.sh root@IP_DU_VPS:` puis `bash preparer-vps.sh`.)

Le script demande le **domaine** et un **email** (alertes du certificat), puis installe :

| Élément | Réglage |
|---|---|
| Utilisateur `deploy` | propriétaire du site ; sudo limité au rechargement de PHP/Nginx |
| SSH | clé uniquement, `root` interdit (si une clé est présente) |
| Pare-feu UFW | seuls 22, 80, 443 ouverts |
| fail2ban | 5 échecs SSH → 1 h de bannissement ; récidive → 1 semaine |
| Mises à jour | correctifs de sécurité installés automatiquement |
| PHP 8.3 + Composer | installeur Composer vérifié par signature |
| MariaDB | écoute locale uniquement ; base + utilisateur dédiés, mot de passe aléatoire |
| Application | `/var/www/jci`, `.env` de production, migrations, caches |
| Nginx | seul `index.php` exécutable ; `.env`, `.git`, fichiers PHP envoyés → refusés |
| HTTPS | Let's Encrypt, redirection automatique, renouvellement automatique |
| Sauvegardes | chaque nuit 2 h 30 : base + photos/logos, 14 jours, `/var/backups/jci` |

Le script peut être **relancé sans danger** (ex. une fois le DNS propagé pour le HTTPS).

## 2. Configurer l'email (en `deploy`)

```bash
ssh deploy@ton-domaine
nano /var/www/jci/.env        # MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS
cd /var/www/jci && php artisan config:cache
```

## 3. Créer le super administrateur

```bash
cd /var/www/jci && php artisan jci:installer
```
Saisis ton email : tu reçois le lien **Activer mon compte** et tu choisis ton mot de passe.

## Mettre à jour le site

Après chaque `git push` sur `main` :
```bash
ssh deploy@ton-domaine
jci-deployer
```
(maintenance le temps de la mise à jour, dépendances, migrations, caches, remise en ligne —
même en cas d'erreur, le site ressort du mode maintenance.)

## En cas de besoin

| Besoin | Commande |
|---|---|
| Journaux de l'application | `tail -50 /var/www/jci/storage/logs/laravel-*.log` |
| Journaux Nginx | `sudo tail -50 /var/log/nginx/jci.error.log` (en root) |
| Sauvegarde immédiate | en root : `/usr/local/bin/jci-sauvegarde` |
| Restaurer la base | en root : `zcat /var/backups/jci/bd_DATE.sql.gz \| mariadb --defaults-extra-file=/root/.jci-bd.cnf jci_djougou` |
| IP bannies par fail2ban | en root : `fail2ban-client status sshd` |
| Ouvrir l'espace aux membres | `JCI_ACCES_MEMBRES=true` dans `.env`, puis `php artisan config:cache` |

**Copie les sauvegardes hors du VPS** régulièrement (ex. `scp` vers ton Mac) : une sauvegarde
restée sur le serveur ne protège pas contre la perte du serveur.
