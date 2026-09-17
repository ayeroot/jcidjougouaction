# Configuration de l'envoi d'e-mails (SMTP)

La plateforme envoie des e-mails pour :

- l'**activation d'un compte** (création du mot de passe par le membre) ;
- la **réinitialisation du mot de passe** ;
- d'éventuelles notifications.

> **Ne mettez jamais de vrais mots de passe ou secrets dans le dépôt.**
> Toute la configuration passe par le fichier `.env` (non versionné). `.env.example`
> liste les variables nécessaires sans valeurs secrètes.

---

## 1. Variables d'environnement

Dans votre fichier `.env` :

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.exemple.com
MAIL_PORT=587
MAIL_USERNAME=votre_utilisateur
MAIL_PASSWORD=votre_mot_de_passe_ou_mot_de_passe_application
MAIL_ENCRYPTION=tls           # tls (port 587) ou ssl (port 465)
MAIL_FROM_ADDRESS="no-reply@jcidjougou.bj"
MAIL_FROM_NAME="JCI Djougou Action"
```

| Variable | Rôle |
|----------|------|
| `MAIL_MAILER` | `smtp` en production ; `log` en développement (écrit l'email dans les logs). |
| `MAIL_HOST` | Serveur SMTP de votre fournisseur. |
| `MAIL_PORT` | `587` (TLS) le plus souvent, ou `465` (SSL). |
| `MAIL_USERNAME` | Identifiant SMTP (souvent l'adresse email complète). |
| `MAIL_PASSWORD` | Mot de passe SMTP **ou mot de passe d'application** (voir Gmail/Outlook). |
| `MAIL_ENCRYPTION` | `tls` pour le port 587, `ssl` pour le port 465. |
| `MAIL_FROM_ADDRESS` | Adresse expéditrice affichée. |
| `MAIL_FROM_NAME` | Nom de l'expéditeur. |

> Sous Laravel 11+, à la place de `MAIL_ENCRYPTION` vous pouvez voir `MAIL_SCHEME`
> (`smtp` = non chiffré/STARTTLS, `smtps` = SSL). Les deux formes fonctionnent ;
> en cas de doute, gardez `MAIL_PORT` + `MAIL_ENCRYPTION` comme ci-dessus.

Après toute modification du `.env` :

```bash
php artisan config:clear
```

---

## 2. Gmail

1. Activez la **validation en deux étapes** sur le compte Google.
2. Créez un **mot de passe d'application** : Compte Google → Sécurité → Mots de passe des applications.
3. Configuration :

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votreadresse@gmail.com
MAIL_PASSWORD=le_mot_de_passe_application_16_caracteres
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="votreadresse@gmail.com"
MAIL_FROM_NAME="JCI Djougou Action"
```

> Le mot de passe **normal** Gmail ne fonctionne pas : il faut un mot de passe d'application.

---

## 3. Outlook / Microsoft 365

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=votreadresse@outlook.com
MAIL_PASSWORD=votre_mot_de_passe_ou_mot_de_passe_application
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="votreadresse@outlook.com"
MAIL_FROM_NAME="JCI Djougou Action"
```

> Si l'authentification échoue, activez SMTP AUTH pour la boîte et/ou créez un
> mot de passe d'application (si la double authentification est active).

---

## 4. Fournisseurs recommandés en production

Pour une meilleure délivrabilité, un service d'envoi transactionnel est conseillé
(**Brevo/Sendinblue**, **Mailgun**, **Postmark**, **Amazon SES**). Exemple Brevo :

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=xxxxxx@smtp-brevo.com
MAIL_PASSWORD=votre_cle_smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@jcidjougou.bj"
MAIL_FROM_NAME="JCI Djougou Action"
```

---

## 5. Tester l'envoi

En développement, laissez `MAIL_MAILER=log` : les emails sont écrits dans
`storage/logs/laravel.log` (aucune configuration requise). Pour lire le dernier email :

```bash
# Windows PowerShell
Get-Content storage/logs/laravel.log -Tail 60
# macOS / Linux
tail -n 60 storage/logs/laravel.log
```

Pour tester un vrai envoi SMTP depuis la console :

```bash
php artisan tinker
>>> Mail::raw('Test SMTP JCI', fn($m) => $m->to('votre@email.com')->subject('Test'));
```

Un envoi sans exception signifie que la configuration SMTP est correcte.

---

## 6. Diagnostiquer les erreurs SMTP

| Erreur / symptôme | Cause probable | Solution |
|-------------------|----------------|----------|
| `Connection could not be established` | Mauvais `MAIL_HOST`/`MAIL_PORT`, pare-feu | Vérifier host/port ; autoriser le port sortant 587/465. |
| `Expected response 220` / timeout | Port bloqué par l'hébergeur | Essayer 465 (ssl) ou demander l'ouverture du port. |
| `535 Authentication failed` | Identifiants incorrects | Vérifier `MAIL_USERNAME`/`MAIL_PASSWORD` ; utiliser un mot de passe d'application. |
| Emails partis mais non reçus | Spam / SPF-DKIM manquants | Configurer SPF/DKIM du domaine ; utiliser un service transactionnel. |
| Rien ne se passe | `MAIL_MAILER=log` | Normal en dev : l'email est dans `storage/logs/laravel.log`. |
| Changement `.env` sans effet | Config en cache | `php artisan config:clear`. |

### Vérifier les logs

Toutes les erreurs d'envoi sont journalisées dans `storage/logs/laravel.log`.
Dans l'application, si un email d'activation échoue, un message le signale à l'administrateur.

---

## 7. Où c'est utilisé dans le code

- `app/Mail/ActivationCompte.php` + `resources/views/emails/activation.blade.php` — email d'activation.
- `app/Services/AccountService.php` — envoi de l'activation à la création du compte.
- Réinitialisation : notification Laravel `ResetPassword` (URL personnalisée dans `app/Providers/AppServiceProvider.php`).
