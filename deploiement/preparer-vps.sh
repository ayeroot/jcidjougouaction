#!/usr/bin/env bash
# =============================================================================
#  JCI Djougou Action — préparation d'un VPS neuf (Ubuntu 22.04 / 24.04, Debian 12)
# =============================================================================
#  À lancer UNE fois, en root, sur le VPS :
#      bash preparer-vps.sh
#  Le script est ré-exécutable sans danger (ex. pour obtenir le certificat HTTPS
#  une fois le DNS propagé) : ce qui est déjà en place n'est pas refait.
#
#  Il installe et configure :
#    - utilisateur « deploy », SSH par clé uniquement, pare-feu UFW, fail2ban,
#      mises à jour de sécurité automatiques ;
#    - Nginx, PHP-FPM, MariaDB (base + utilisateur dédiés), Composer, Certbot ;
#    - l'application (clone Git, .env de production, migrations, caches) ;
#    - HTTPS Let's Encrypt, sauvegardes quotidiennes, rotation des logs,
#      commande de mise à jour « jci-deployer ».
#
#  Variables (sinon demandées) :
#    DOMAINE      ex. jcidjougou.org          (obligatoire)
#    EMAIL_SSL    email pour Let's Encrypt     (obligatoire)
#    DEPOT        URL Git du projet            (défaut : GitHub ayeroot/jcidjougouaction)
#    BRANCHE      branche déployée             (défaut : main)
#    AVEC_WWW     oui/non : servir aussi www.  (défaut : oui)
#    NON_INTERACTIF=1 : ne rien demander (valeurs par défaut / variables)
# =============================================================================
set -Eeuo pipefail

# ---------- Réglages ---------------------------------------------------------
PHP_VERSION="${PHP_VERSION:-8.3}"
UTILISATEUR="${UTILISATEUR:-deploy}"
DOSSIER="${DOSSIER:-/var/www/jci}"
BASE_DONNEES="${BASE_DONNEES:-jci_djougou}"
UTILISATEUR_BD="${UTILISATEUR_BD:-jci_app}"
DEPOT="${DEPOT:-https://github.com/ayeroot/jcidjougouaction.git}"
BRANCHE="${BRANCHE:-main}"
AVEC_WWW="${AVEC_WWW:-oui}"
FUSEAU="Africa/Porto-Novo"
JOURNAL="/var/log/jci-preparation.log"

# ---------- Affichage ----------------------------------------------------------
V=$'\e[32m'; J=$'\e[33m'; R=$'\e[31m'; B=$'\e[1m'; N=$'\e[0m'
etape() { echo; echo "${B}==> $*${N}"; }
ok()    { echo "  ${V}✓${N} $*"; }
alerte(){ echo "  ${J}!${N} $*"; }
echec() { echo "  ${R}✗ $*${N}" >&2; exit 1; }
trap 'echo "${R}Erreur ligne $LINENO (voir $JOURNAL).${N}" >&2' ERR
exec > >(tee -a "$JOURNAL") 2>&1

demander() { # demander VAR "question" [défaut]
    local var="$1" question="$2" defaut="${3:-}"
    if [[ -n "${!var:-}" ]]; then return; fi
    if [[ "${NON_INTERACTIF:-0}" == "1" ]]; then printf -v "$var" '%s' "$defaut"; return; fi
    local rep; read -r -p "  $question${defaut:+ [$defaut]} : " rep
    printf -v "$var" '%s' "${rep:-$defaut}"
}
installer() { apt-get -y -qq -o Dpkg::Use-Pty=0 install "$@" >/dev/null; }
en_deploy() { sudo -u "$UTILISATEUR" -H bash -c "cd '$DOSSIER' && $*"; }
env_valeur() { grep -E "^$1=" "$DOSSIER/.env" 2>/dev/null | head -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'; }
env_mettre() { # env_mettre CLE valeur  (remplace ou ajoute dans .env)
    local cle="$1" val="$2" f="$DOSSIER/.env"
    if grep -qE "^$cle=" "$f"; then
        python3 - "$f" "$cle" "$val" <<'PY'
import sys; f,k,v=sys.argv[1:]; L=open(f).read().splitlines()
open(f,'w').write("\n".join(f"{k}={v}" if l.startswith(k+"=") else l for l in L)+"\n")
PY
    else echo "$cle=$val" >> "$f"; fi
}

# ---------- 0. Vérifications ---------------------------------------------------
etape "Vérifications"
[[ $EUID -eq 0 ]] || echec "Lancez ce script en root (sudo -i puis bash preparer-vps.sh)."
. /etc/os-release
case "$ID:$VERSION_ID" in
    ubuntu:22.04|ubuntu:24.04|debian:12) ok "Système : $PRETTY_NAME" ;;
    *) echec "Système non prévu : $PRETTY_NAME (Ubuntu 22.04/24.04 ou Debian 12 attendus)." ;;
esac
demander DOMAINE   "Nom de domaine du site (ex. jcidjougou.org)"
demander EMAIL_SSL "Email pour les alertes du certificat HTTPS"
[[ "$DOMAINE" =~ ^[a-z0-9.-]+\.[a-z]{2,}$ ]] || echec "Domaine invalide : « $DOMAINE »."
[[ "$EMAIL_SSL" == *@*.* ]] || echec "Email invalide : « $EMAIL_SSL »."
DOMAINE="${DOMAINE,,}"
ok "Domaine : $DOMAINE — dépôt : $DEPOT ($BRANCHE)"

# ---------- 1. Système -----------------------------------------------------------
etape "Mise à jour du système et paquets de base"
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get -y -qq -o Dpkg::Use-Pty=0 -o Dpkg::Options::=--force-confold upgrade >/dev/null
installer ca-certificates curl gnupg lsb-release git unzip acl cron \
    ufw fail2ban unattended-upgrades logrotate python3 openssl sudo tzdata
if command -v timedatectl >/dev/null && timedatectl set-timezone "$FUSEAU" 2>/dev/null; then :; else
    ln -sf "/usr/share/zoneinfo/$FUSEAU" /etc/localtime; echo "$FUSEAU" > /etc/timezone; fi
ok "Fuseau horaire : $FUSEAU"
echo 'APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";' > /etc/apt/apt.conf.d/20auto-upgrades
ok "Mises à jour de sécurité automatiques activées"

# ---------- 2. PHP ---------------------------------------------------------------
etape "PHP $PHP_VERSION"
if ! apt-cache show "php$PHP_VERSION-fpm" >/dev/null 2>&1; then
    if [[ "$ID" == "ubuntu" ]]; then
        installer software-properties-common
        add-apt-repository -y ppa:ondrej/php >/dev/null
    else
        curl -fsSL https://packages.sury.org/php/apt.gpg -o /usr/share/keyrings/sury-php.gpg
        echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ $VERSION_CODENAME main" \
            > /etc/apt/sources.list.d/sury-php.list
    fi
    apt-get update -qq
fi
installer "php$PHP_VERSION"-{fpm,cli,mysql,sqlite3,mbstring,xml,curl,zip,bcmath,intl,gd,opcache,readline}
cat > "/etc/php/$PHP_VERSION/fpm/conf.d/99-jci.ini" <<EOF
; JCI Djougou Action
expose_php = Off
memory_limit = 256M
upload_max_filesize = 8M
post_max_size = 10M
max_execution_time = 60
date.timezone = $FUSEAU
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 20000
EOF
cp "/etc/php/$PHP_VERSION/fpm/conf.d/99-jci.ini" "/etc/php/$PHP_VERSION/cli/conf.d/99-jci.ini"
ok "PHP $(php -r 'echo PHP_VERSION;') + extensions Laravel"

if ! command -v composer >/dev/null; then
    ATTENDU="$(curl -fsSL https://composer.github.io/installer.sig)"
    curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
    OBTENU="$(php -r "echo hash_file('sha384', '/tmp/composer-setup.php');")"
    [[ "$ATTENDU" == "$OBTENU" ]] || echec "Installeur Composer corrompu (signature invalide)."
    php /tmp/composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
    rm -f /tmp/composer-setup.php
fi
ok "Composer $(composer --version 2>/dev/null | awk '{print $3}')"

# ---------- 3. Utilisateur deploy + SSH ----------------------------------------------
etape "Utilisateur « $UTILISATEUR » et SSH"
if ! id "$UTILISATEUR" >/dev/null 2>&1; then
    adduser --disabled-password --gecos "Deploiement JCI" "$UTILISATEUR" >/dev/null
fi
usermod -aG www-data "$UTILISATEUR"
install -d -m 700 -o "$UTILISATEUR" -g "$UTILISATEUR" "/home/$UTILISATEUR/.ssh"
if [[ -s /root/.ssh/authorized_keys && ! -s "/home/$UTILISATEUR/.ssh/authorized_keys" ]]; then
    cp /root/.ssh/authorized_keys "/home/$UTILISATEUR/.ssh/authorized_keys"
fi
[[ -f "/home/$UTILISATEUR/.ssh/authorized_keys" ]] && chown "$UTILISATEUR:$UTILISATEUR" "/home/$UTILISATEUR/.ssh/authorized_keys" && chmod 600 "/home/$UTILISATEUR/.ssh/authorized_keys"
# Droits sudo limités : recharger PHP-FPM et Nginx (pour jci-deployer), rien d'autre.
echo "$UTILISATEUR ALL=(root) NOPASSWD: /usr/bin/systemctl reload php$PHP_VERSION-fpm, /usr/bin/systemctl reload nginx" \
    > /etc/sudoers.d/jci-deploy
chmod 440 /etc/sudoers.d/jci-deploy
visudo -cf /etc/sudoers.d/jci-deploy >/dev/null
ok "Utilisateur $UTILISATEUR prêt (sudo limité au rechargement de PHP/Nginx)"

# Clé SSH du serveur pour GitHub (dépôt privé : à ajouter en « Deploy key », lecture seule)
if [[ ! -f "/home/$UTILISATEUR/.ssh/id_ed25519" ]]; then
    sudo -u "$UTILISATEUR" ssh-keygen -q -t ed25519 -N "" -C "jci-vps-$DOMAINE" -f "/home/$UTILISATEUR/.ssh/id_ed25519"
fi
sudo -u "$UTILISATEUR" bash -c 'ssh-keyscan -t ed25519 github.com >> ~/.ssh/known_hosts 2>/dev/null; sort -u -o ~/.ssh/known_hosts ~/.ssh/known_hosts'

# Durcissement SSH : UNIQUEMENT si une clé permet déjà de se connecter en « deploy »
# (sinon on risquerait de vous enfermer dehors).
if [[ -s "/home/$UTILISATEUR/.ssh/authorized_keys" ]]; then
    cat > /etc/ssh/sshd_config.d/99-jci.conf <<'EOF'
# JCI : connexion par clé uniquement, pas de root direct
PermitRootLogin no
PasswordAuthentication no
KbdInteractiveAuthentication no
MaxAuthTries 4
X11Forwarding no
EOF
    if sshd -t 2>/dev/null; then
        systemctl reload ssh 2>/dev/null || systemctl reload sshd 2>/dev/null || service ssh reload 2>/dev/null || true
        ok "SSH durci : clé obligatoire, root interdit → connectez-vous désormais avec : ssh $UTILISATEUR@$DOMAINE"
    else
        rm -f /etc/ssh/sshd_config.d/99-jci.conf; alerte "Configuration SSH refusée par sshd -t : durcissement annulé."
    fi
else
    alerte "Aucune clé SSH trouvée pour $UTILISATEUR : SSH NON durci (mot de passe root toujours accepté)."
    alerte "Ajoutez votre clé (ssh-copy-id root@IP depuis votre Mac) puis relancez ce script."
fi

# ---------- 4. Pare-feu + fail2ban ----------------------------------------------------
etape "Pare-feu et protection contre les attaques par force brute"
ufw default deny incoming >/dev/null
ufw default allow outgoing >/dev/null
ufw allow OpenSSH >/dev/null
ufw allow 80/tcp >/dev/null
ufw allow 443/tcp >/dev/null
if ufw --force enable >/dev/null 2>&1; then ok "UFW actif : seuls SSH, 80 et 443 sont ouverts"
else alerte "UFW n'a pas pu être activé (conteneur ?) : vérifiez « ufw status »."; fi
cat > /etc/fail2ban/jail.d/jci.local <<'EOF'
[sshd]
enabled  = true
maxretry = 5
findtime = 10m
bantime  = 1h

[recidive]
enabled  = true
bantime  = 1w
findtime = 1d
EOF
systemctl enable --now fail2ban >/dev/null 2>&1 || service fail2ban restart >/dev/null 2>&1 || true
systemctl restart fail2ban >/dev/null 2>&1 || true
ok "fail2ban : 5 échecs SSH = 1 h de bannissement, récidive = 1 semaine"

# ---------- 5. MariaDB --------------------------------------------------------------------
etape "Base de données MariaDB"
installer mariadb-server
systemctl enable --now mariadb >/dev/null 2>&1 || service mariadb start >/dev/null 2>&1 || true
# Écoute locale uniquement (jamais exposée sur Internet).
cat > /etc/mysql/mariadb.conf.d/99-jci.cnf <<'EOF'
[mysqld]
bind-address = 127.0.0.1
local-infile = 0
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
EOF
systemctl restart mariadb >/dev/null 2>&1 || service mariadb restart >/dev/null 2>&1 || true
# Nettoyage type mysql_secure_installation
mariadb -e "DELETE FROM mysql.global_priv WHERE User=''; DROP DATABASE IF EXISTS test; FLUSH PRIVILEGES;" 2>/dev/null || true

MDP_BD="$(env_valeur DB_PASSWORD || true)"
if [[ -z "$MDP_BD" ]]; then MDP_BD="$(openssl rand -base64 36 | tr -dc 'A-Za-z0-9' | head -c 32)"; fi
mariadb <<EOF
CREATE DATABASE IF NOT EXISTS \`$BASE_DONNEES\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$UTILISATEUR_BD'@'localhost' IDENTIFIED BY '$MDP_BD';
ALTER USER '$UTILISATEUR_BD'@'localhost' IDENTIFIED BY '$MDP_BD';
GRANT ALL PRIVILEGES ON \`$BASE_DONNEES\`.* TO '$UTILISATEUR_BD'@'localhost';
FLUSH PRIVILEGES;
EOF
# Identifiants pour les sauvegardes (lisibles par root seulement)
umask 077
printf '[client]\nuser=%s\npassword=%s\n' "$UTILISATEUR_BD" "$MDP_BD" > /root/.jci-bd.cnf
umask 022
ok "Base « $BASE_DONNEES » + utilisateur « $UTILISATEUR_BD » (accès local uniquement, mot de passe aléatoire)"

# ---------- 6. Application ------------------------------------------------------------------
etape "Application"
install -d -o "$UTILISATEUR" -g www-data -m 2750 "$DOSSIER"
if [[ ! -d "$DOSSIER/.git" ]]; then
    if ! sudo -u "$UTILISATEUR" -H env GIT_TERMINAL_PROMPT=0 git clone -q --branch "$BRANCHE" "$DEPOT" "$DOSSIER" 2>/dev/null; then
        echo
        alerte "Clonage impossible : le dépôt est probablement privé."
        alerte "Ajoutez cette clé dans GitHub → dépôt → Settings → Deploy keys (lecture seule) :"
        echo; cat "/home/$UTILISATEUR/.ssh/id_ed25519.pub"; echo
        alerte "Puis relancez avec l'adresse SSH du dépôt, par exemple :"
        alerte "  DEPOT=git@github.com:ayeroot/jcidjougouaction.git bash preparer-vps.sh"
        exit 1
    fi
fi
# Les droits appliqués plus bas (640/750) ne doivent pas apparaître comme des
# modifications locales, sinon « git merge » refuserait les mises à jour.
en_deploy "git config core.fileMode false"
ok "Code récupéré dans $DOSSIER ($(en_deploy git log --oneline -1))"

en_deploy "composer install --no-dev --optimize-autoloader --no-interaction --no-progress -q"
ok "Dépendances PHP installées"

if [[ ! -f "$DOSSIER/.env" ]]; then
    sudo -u "$UTILISATEUR" tee "$DOSSIER/.env" >/dev/null <<EOF
APP_NAME="JCI Djougou Action"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://$DOMAINE
APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$BASE_DONNEES
DB_USERNAME=$UTILISATEUR_BD
DB_PASSWORD=$MDP_BD

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=false
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

# ---- À COMPLÉTER : SMTP (voir SMTP_SETUP.md), puis « php artisan config:cache » ----
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_SCHEME=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="no-reply@$DOMAINE"
MAIL_FROM_NAME="JCI Djougou Action"

# Accès des simples membres (false : seuls l'administration et le CDL se connectent)
JCI_ACCES_MEMBRES=false
# VPS : Nginx reçoit directement les visiteurs -> vide. Derrière Cloudflare : *
TRUSTED_PROXIES=
EOF
    ok ".env de production créé"
else
    ok ".env existant conservé"
fi
chown "$UTILISATEUR:www-data" "$DOSSIER/.env"; chmod 640 "$DOSSIER/.env"
[[ -n "$(env_valeur APP_KEY)" ]] || en_deploy "php artisan key:generate --force -q"
ok "APP_KEY en place"

# Droits : code en lecture pour PHP (www-data), écriture seulement dans storage/ et bootstrap/cache/
chown -R "$UTILISATEUR:www-data" "$DOSSIER"
find "$DOSSIER" -path "$DOSSIER/vendor" -prune -o -type d -exec chmod 2750 {} +
find "$DOSSIER" -path "$DOSSIER/vendor" -prune -o -type f -exec chmod 640 {} +
chmod 750 "$DOSSIER/artisan"
for d in storage bootstrap/cache; do
    chmod -R 2770 "$DOSSIER/$d"
    setfacl -R -m u:www-data:rwX -m d:u:www-data:rwX -m u:"$UTILISATEUR":rwX -m d:u:"$UTILISATEUR":rwX "$DOSSIER/$d"
done
chmod -R g+rX "$DOSSIER/vendor" "$DOSSIER/public"
en_deploy "php artisan storage:link -q 2>/dev/null || true"
en_deploy "php artisan migrate --force -q"
en_deploy "php artisan optimize -q"
ok "Migrations appliquées, caches de production générés"

# ---------- 7. Nginx ------------------------------------------------------------------------
etape "Nginx"
installer nginx
NOMS="$DOMAINE"; [[ "$AVEC_WWW" == "oui" ]] && NOMS="$DOMAINE www.$DOMAINE"
# IPv6 seulement si le serveur le prend en charge (certains VPS n'en ont pas)
ECOUTE_V6=""; [[ -s /proc/net/if_inet6 ]] && ECOUTE_V6="    listen [::]:80;"
cat > /etc/nginx/conf.d/00-jci-securite.conf <<'EOF'
server_tokens off;
client_max_body_size 10M;
EOF
if [[ ! -f /etc/nginx/sites-available/jci ]] || ! grep -q "managed by Certbot" /etc/nginx/sites-available/jci; then
cat > /etc/nginx/sites-available/jci <<EOF
server {
    listen 80;
$ECOUTE_V6
    server_name $NOMS;
    root $DOSSIER/public;
    index index.php;
    charset utf-8;

    access_log /var/log/nginx/jci.access.log;
    error_log  /var/log/nginx/jci.error.log warn;

    gzip on;
    gzip_types text/css application/javascript image/svg+xml application/json;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ ^/index\.php(/|\$) {
        fastcgi_pass unix:/run/php/php$PHP_VERSION-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)\$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT \$realpath_root;
        fastcgi_hide_header X-Powered-By;
        internal;
    }

    # Aucun autre fichier PHP (ni exécuté, ni affiché), aucun fichier caché (.env, .git…)
    location ~ \.php\$ { return 404; }
    location ~ /\.(?!well-known) { deny all; }

    # Assets compilés (noms uniques) et images : cache navigateur long
    location ~* ^/(build|images|storage)/ {
        expires 30d;
        add_header Cache-Control "public";
        try_files \$uri =404;
    }
}
EOF
fi
ln -sf /etc/nginx/sites-available/jci /etc/nginx/sites-enabled/jci
rm -f /etc/nginx/sites-enabled/default
nginx -t >/dev/null 2>&1 || { nginx -t; echec "Configuration Nginx invalide."; }
systemctl enable --now "php$PHP_VERSION-fpm" nginx >/dev/null 2>&1 || { service "php$PHP_VERSION-fpm" start; service nginx start; } >/dev/null 2>&1 || true
systemctl reload "php$PHP_VERSION-fpm" nginx >/dev/null 2>&1 || { service "php$PHP_VERSION-fpm" reload; service nginx reload; } >/dev/null 2>&1 || true
ok "Site servi depuis $DOSSIER/public ($NOMS)"

# ---------- 8. HTTPS ----------------------------------------------------------------------------
etape "Certificat HTTPS (Let's Encrypt)"
installer certbot python3-certbot-nginx
IP_SERVEUR="$(curl -fsS4 --max-time 10 https://api.ipify.org || true)"
IP_DNS="$(getent ahostsv4 "$DOMAINE" | awk 'NR==1{print $1}' || true)"
if [[ -n "$IP_SERVEUR" && "$IP_SERVEUR" == "$IP_DNS" ]]; then
    DOMAINES_CERT=(-d "$DOMAINE")
    if [[ "$AVEC_WWW" == "oui" ]]; then
        if [[ "$(getent ahostsv4 "www.$DOMAINE" | awk 'NR==1{print $1}')" == "$IP_SERVEUR" ]]; then
            DOMAINES_CERT+=(-d "www.$DOMAINE")
        else
            alerte "www.$DOMAINE ne pointe pas encore vers ce serveur : certificat pour $DOMAINE seul."
        fi
    fi
    certbot --nginx --non-interactive --agree-tos -m "$EMAIL_SSL" --redirect --keep-until-expiring "${DOMAINES_CERT[@]}" >/dev/null
    env_mettre SESSION_SECURE_COOKIE true
    en_deploy "php artisan config:cache -q"
    ok "HTTPS actif sur https://$DOMAINE (renouvellement automatique)"
    HTTPS_OK=1
else
    HTTPS_OK=0
    alerte "Le DNS de $DOMAINE pointe vers « ${IP_DNS:-rien} », ce serveur est « ${IP_SERVEUR:-inconnu} »."
    alerte "Créez un enregistrement A : $DOMAINE -> ${IP_SERVEUR:-IP du VPS} (et www), attendez la propagation,"
    alerte "puis relancez « bash preparer-vps.sh » : le certificat sera obtenu automatiquement."
fi

# ---------- 9. Tâches planifiées, sauvegardes, logs, déploiement --------------------------------
etape "Tâches planifiées, sauvegardes et mise à jour"
echo "* * * * * cd $DOSSIER && php artisan schedule:run >> /dev/null 2>&1" | crontab -u "$UTILISATEUR" -

cat > /usr/local/bin/jci-sauvegarde <<EOF
#!/usr/bin/env bash
# Sauvegarde quotidienne : base de données + fichiers envoyés (photos, logos). Conservation 14 jours.
set -euo pipefail
CIBLE=/var/backups/jci; DATE=\$(date +%Y-%m-%d_%H%M)
install -d -m 700 "\$CIBLE"
mariadb-dump --defaults-extra-file=/root/.jci-bd.cnf --single-transaction --routines "$BASE_DONNEES" | gzip > "\$CIBLE/bd_\$DATE.sql.gz"
tar -czf "\$CIBLE/fichiers_\$DATE.tar.gz" -C "$DOSSIER/storage/app" public
find "\$CIBLE" -type f -mtime +14 -delete
EOF
chmod 750 /usr/local/bin/jci-sauvegarde
echo "30 2 * * * root /usr/local/bin/jci-sauvegarde >> /var/log/jci-sauvegarde.log 2>&1" > /etc/cron.d/jci-sauvegarde
ok "Sauvegarde chaque nuit à 2 h 30 dans /var/backups/jci (14 jours)"

cat > /etc/logrotate.d/jci <<EOF
$DOSSIER/storage/logs/*.log {
    weekly
    rotate 8
    compress
    missingok
    notifempty
    copytruncate
}
EOF

cat > /usr/local/bin/jci-deployer <<EOF
#!/usr/bin/env bash
# Mise à jour du site : à lancer en « $UTILISATEUR » (ssh $UTILISATEUR@$DOMAINE puis jci-deployer)
set -Eeuo pipefail
[[ "\$(id -un)" == "$UTILISATEUR" ]] || { echo "Lancez cette commande en tant que $UTILISATEUR."; exit 1; }
cd "$DOSSIER"
echo "==> Mode maintenance"; php artisan down --retry=30 || true
trap 'php artisan up >/dev/null 2>&1 || true' EXIT
echo "==> Récupération du code ($BRANCHE)"
git fetch --prune -q origin
git merge --ff-only -q "origin/$BRANCHE"
echo "==> Dépendances"; composer install --no-dev --optimize-autoloader --no-interaction --no-progress -q
echo "==> Base de données"; php artisan migrate --force
echo "==> Caches"; php artisan optimize:clear -q; php artisan optimize -q
sudo -n /usr/bin/systemctl reload php$PHP_VERSION-fpm 2>/dev/null || echo "  (rechargement de PHP-FPM impossible : sans gravité, PHP relit les fichiers modifiés)"
php artisan up
echo "✓ Déployé : \$(git log --oneline -1)"
EOF
chmod 755 /usr/local/bin/jci-deployer
ok "Commande de mise à jour installée : jci-deployer"

# ---------- 10. Vérification finale -------------------------------------------------------------
etape "Vérification"
CODE="$(curl -s -o /dev/null -w '%{http_code}' -H "Host: $DOMAINE" http://127.0.0.1/connexion || true)"
case "$CODE" in
    200|301|302) ok "Le site répond (HTTP $CODE)" ;;
    *) alerte "Le site répond HTTP $CODE : voir /var/log/nginx/jci.error.log et $DOSSIER/storage/logs/" ;;
esac

echo
echo "${B}==================== PRÉPARATION TERMINÉE ====================${N}"
cat <<EOF

 Il reste 2 étapes, en tant que $UTILISATEUR :

   1. Renseigner le SMTP (nouveau mot de passe) :
        nano $DOSSIER/.env          -> MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD…
        cd $DOSSIER && php artisan config:cache

   2. Créer le super administrateur (il reçoit son lien d'activation par email) :
        cd $DOSSIER && php artisan jci:installer

 Mises à jour ensuite :  jci-deployer
 Sauvegarde manuelle   :  /usr/local/bin/jci-sauvegarde        (en root : sudo -i)
 Journal complet       :  $JOURNAL
EOF
[[ "$HTTPS_OK" == 1 ]] || echo " ${J}HTTPS pas encore actif : pointez le DNS puis relancez ce script.${N}"
echo
