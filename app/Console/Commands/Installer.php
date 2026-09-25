<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AccountService;
use App\Support\Journal;
use App\Support\Permissions;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Première mise en service de la plateforme.
 *
 *   php artisan jci:installer
 *
 * 1. vérifie la configuration (URL, email) ;
 * 2. crée les rôles et permissions (aucune donnée de démo) ;
 * 3. crée LE compte super administrateur avec l'email saisi ;
 * 4. envoie à cette adresse le lien d'activation : le mot de passe est choisi par
 *    son propriétaire, il n'est jamais affiché ni transmis par personne d'autre.
 *
 * Relancer la commande tant que le compte n'est pas activé renvoie un nouveau lien.
 */
class Installer extends Command
{
    protected $signature = 'jci:installer
        {--email= : Email du super administrateur (sinon demandé)}
        {--nom= : Nom affiché (défaut : « Super administrateur »)}
        {--migrer : Exécuter aussi les migrations}';

    protected $description = 'Installe la plateforme : rôles, permissions et compte super administrateur (activation par email)';

    public function handle(AccountService $comptes): int
    {
        $this->line('');
        $this->info('Installation — JCI Djougou Action');
        $this->line('');

        if (! $this->verifierConfiguration()) {
            return self::FAILURE;
        }

        if ($this->option('migrer')) {
            $this->call('migrate', ['--force' => true]);
        }

        $this->callSilently('db:seed', ['--class' => RolesPermissionsSeeder::class, '--force' => true]);
        $this->line('  ✓ Rôles et permissions en place');

        // Un super administrateur existe déjà ?
        $existant = User::role(Permissions::ROLE_SUPER)->first();
        if ($existant) {
            if ($existant->actif && is_null($existant->activation_token)) {
                $this->info("  ✓ Déjà installé : le super administrateur ({$existant->email}) est actif.");
                $this->line('    Mot de passe perdu ? Utilisez « Mot de passe oublié » sur la page de connexion.');
                return self::SUCCESS;
            }

            $this->warn("  Le super administrateur {$existant->email} n'a pas encore activé son compte.");
            if (! $this->option('no-interaction') && ! $this->confirm('Renvoyer un nouveau lien d\'activation ?', true)) {
                return self::SUCCESS;
            }
            return $this->envoyer($comptes, $existant);
        }

        // Email du super administrateur
        $email = $this->option('email') ?: $this->ask('Email du super administrateur (il recevra le lien d\'activation)');
        $email = Str::lower(trim((string) $email));

        $v = Validator::make(['email' => $email], ['email' => ['required', 'email:rfc', 'max:255', 'unique:users,email']], [
            'email.unique' => 'Cette adresse est déjà utilisée par un autre compte.',
        ]);
        if ($v->fails()) {
            $this->error('  '.$v->errors()->first('email'));
            return self::FAILURE;
        }

        $nom = $this->option('nom') ?: ($this->option('no-interaction')
            ? 'Super administrateur'
            : $this->ask('Nom affiché', 'Super administrateur'));

        // Compte inactif, mot de passe aléatoire inutilisable : c'est le propriétaire
        // de l'adresse qui choisit le sien via le lien d'activation.
        $user = new User(['name' => $nom, 'email' => $email]);
        $user->password = Hash::make(Str::random(64));
        $user->actif = false;
        $user->save();
        $user->assignRole(Permissions::ROLE_SUPER);

        Journal::ecrire('INSTALLATION', User::class, $user->id, [], ['email' => $email], $user);
        $this->line("  ✓ Compte super administrateur créé : {$email}");

        return $this->envoyer($comptes, $user);
    }

    private function envoyer(AccountService $comptes, User $user): int
    {
        if ($comptes->envoyerActivation($user)) {
            $this->info("  ✓ Lien d'activation envoyé à {$user->email} (valable ".AccountService::ACTIVATION_HEURES.' h).');
            $this->line('    Ouvrez l\'email, cliquez sur « Activer mon compte » et choisissez votre mot de passe.');
            return self::SUCCESS;
        }

        $this->error("  ✗ L'email n'a pas pu être envoyé à {$user->email}.");
        $this->line('    Vérifiez MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD (voir SMTP_SETUP.md),');
        $this->line('    puis relancez « php artisan jci:installer » pour renvoyer le lien.');
        return self::FAILURE;
    }

    private function verifierConfiguration(): bool
    {
        $ok = true;

        if (empty(config('app.key'))) {
            $this->error('  ✗ APP_KEY est vide : lancez « php artisan key:generate ».');
            $ok = false;
        }

        $url = (string) config('app.url');
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $this->error("  ✗ APP_URL doit commencer par https:// (valeur actuelle : « {$url} »).");
            $ok = false;
        } elseif ($this->laravel->isProduction() && (! str_starts_with($url, 'https://') || str_contains($url, 'localhost') || str_contains($url, '127.0.0.1'))) {
            $this->error("  ✗ En production, APP_URL doit être l'adresse publique en https:// (valeur actuelle : {$url}).");
            $this->line('    Sinon le lien d\'activation envoyé par email ne fonctionnera pas.');
            $ok = false;
        } else {
            $this->line("  ✓ Adresse du site : {$url}");
        }

        $mailer = config('mail.default');
        if (in_array($mailer, ['log', 'array'], true)) {
            if ($this->laravel->isProduction()) {
                $this->error("  ✗ MAIL_MAILER={$mailer} : aucun email ne partira. Configurez le SMTP (SMTP_SETUP.md).");
                $ok = false;
            } else {
                $this->warn("  ! MAIL_MAILER={$mailer} : l'email d'activation sera écrit dans storage/logs/laravel.log (normal en local).");
            }
        } elseif ($mailer === 'smtp' && empty(config('mail.mailers.smtp.host'))) {
            $this->error('  ✗ MAIL_HOST est vide : renseignez le SMTP dans .env (SMTP_SETUP.md), puis « php artisan config:cache ».');
            $ok = false;
        } else {
            $this->line("  ✓ Envoi d'emails : {$mailer} (".config("mail.mailers.$mailer.host", '—').')');
        }

        if ($this->laravel->isProduction() && config('app.debug')) {
            $this->error('  ✗ APP_DEBUG=true en production : mettez APP_DEBUG=false.');
            $ok = false;
        }

        return $ok;
    }
}
