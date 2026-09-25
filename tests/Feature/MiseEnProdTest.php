<?php

namespace Tests\Feature;

use App\Mail\ActivationCompte;
use App\Mail\AlerteChangementEmail;
use App\Mail\ConfirmationEmail;
use App\Models\Mandat;
use App\Models\Membre;
use App\Models\Postulant;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Mise en production : installation, super administrateur, accès membres fermé,
 * avatars, activation des postes du CDL, changement d'email en libre-service.
 */
class MiseEnProdTest extends TestCase
{
    use RefreshDatabase;

    private function viderCache(): void
    {
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function seedDemo(): void
    {
        $this->viderCache();
        $this->seed();
        $this->viderCache();
    }

    private function jetonActivation(string $email): string
    {
        $jeton = null;
        Mail::assertSent(ActivationCompte::class, function ($m) use ($email, &$jeton) {
            if ($m->hasTo($email)) { $jeton = $m->token; return true; }
            return false;
        });
        return $jeton;
    }

    /* ================= Installation / super administrateur ================= */

    public function test_installation_cree_le_super_admin_et_envoie_le_lien(): void
    {
        Mail::fake();
        $this->viderCache();

        $this->artisan('jci:installer', ['--email' => 'Dev@Exemple.bj', '--no-interaction' => true])
            ->expectsOutputToContain("Lien d'activation envoyé à dev@exemple.bj")
            ->doesntExpectOutputToContain('mot de passe :')
            ->assertSuccessful();

        $super = User::where('email', 'dev@exemple.bj')->firstOrFail();
        $this->assertTrue($super->hasRole(Permissions::ROLE_SUPER));
        $this->assertFalse($super->actif, 'le compte reste inactif tant que le lien n\'est pas utilisé');

        // Le propriétaire de l'adresse choisit son mot de passe via le lien.
        $jeton = $this->jetonActivation('dev@exemple.bj');
        $this->post(route('activation.store'), [
            'token' => $jeton, 'password' => 'MonMotDePasse2026', 'password_confirmation' => 'MonMotDePasse2026',
        ])->assertRedirect(route('dashboard'));

        $super->refresh();
        $this->assertTrue($super->actif);
        $this->assertTrue(Hash::check('MonMotDePasse2026', $super->password));

        // Tout-puissant : finances et administration comprises.
        $this->viderCache();
        $this->actingAs($super)->get(route('finances.index'))->assertOk();
        $this->actingAs($super)->get(route('admin.roles.index'))->assertOk();
        $this->assertTrue($super->can('finances.gerer'));
    }

    public function test_installation_relancee_renvoie_le_lien_puis_refuse_une_fois_active(): void
    {
        Mail::fake();
        $this->viderCache();
        $this->artisan('jci:installer', ['--email' => 'dev@exemple.bj', '--no-interaction' => true])->assertSuccessful();
        $this->artisan('jci:installer', ['--no-interaction' => true])->assertSuccessful();
        Mail::assertSent(ActivationCompte::class, 2); // lien renvoyé
        $this->assertSame(1, User::role(Permissions::ROLE_SUPER)->count());

        User::role(Permissions::ROLE_SUPER)->first()->forceFill(['actif' => true, 'activation_token' => null])->save();
        $this->artisan('jci:installer', ['--email' => 'autre@exemple.bj', '--no-interaction' => true])
            ->expectsOutputToContain('Déjà installé')->assertSuccessful();
        $this->assertSame(1, User::role(Permissions::ROLE_SUPER)->count());
        $this->assertNull(User::where('email', 'autre@exemple.bj')->first());
    }

    public function test_seed_en_production_ne_cree_aucune_donnee_de_demo(): void
    {
        $this->app['env'] = 'production';
        $this->viderCache();
        $this->artisan('db:seed', ['--force' => true])
            ->expectsOutputToContain('Production : données de démo ignorées')
            ->assertSuccessful();

        $this->assertSame(0, User::count(), 'aucun compte (et donc aucun mot de passe) généré en production');
        $this->assertSame(0, Membre::count());
        $this->assertTrue(\Spatie\Permission\Models\Role::where('name', 'tresorier')->exists());
    }

    public function test_un_admin_ne_peut_pas_toucher_au_super_admin(): void
    {
        Mail::fake();
        $this->seedDemo();
        $this->artisan('jci:installer', ['--email' => 'dev@exemple.bj', '--no-interaction' => true]);
        $super = User::role(Permissions::ROLE_SUPER)->first();
        $admin = User::role('admin')->first();
        $admin->forceFill(['actif' => true])->save();

        $this->actingAs($admin)->get(route('admin.users.edit', $super))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.users.toggle', $super))->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.users.destroy', $super))->assertForbidden();
        $this->assertNotNull($super->fresh());
    }

    /* ================= Accès membres fermé ================= */

    public function test_les_membres_simples_n_ont_ni_compte_ni_acces(): void
    {
        Mail::fake();
        $this->seedDemo();

        // Le seed ne crée aucun compte pour les membres simples.
        $this->assertSame(0, User::role('membre')->count());

        // Inscription publique : aucun email, aucun compte.
        $this->post(route('inscription.store'), ['nom' => 'Test', 'prenom' => 'A', 'telephone' => '0100', 'consentement' => '1']);
        Mail::assertNothingSent();
        $this->assertNull(User::where('name', 'like', '%Test%')->first());

        // Un compte « membre » (ancien) ne peut pas se connecter.
        $m = Membre::where('fonction', 'Membre')->first();
        $u = User::create(['name' => 'M', 'email' => 'membre@exemple.bj', 'password' => 'MotDePasse2026', 'membre_id' => $m->id, 'actif' => true]);
        $u->assignRole('membre');
        $this->post('/connexion', ['email' => 'membre@exemple.bj', 'password' => 'MotDePasse2026'])
            ->assertSessionHasErrors(['email' => "L'espace membre n'est pas encore ouvert à tous les membres."]);
        $this->assertGuest();

        // L'admin ne peut pas créer de compte « membre ».
        $admin = User::role('admin')->first();
        $admin->forceFill(['actif' => true])->save();
        $autre = Membre::whereDoesntHave('user')->whereNotNull('email')->first();
        $this->actingAs($admin)->post(route('admin.users.store'), ['membre_id' => $autre->id, 'role' => 'membre'])
            ->assertSessionHasErrors('role');
    }

    public function test_acces_membres_reouvrable_par_configuration(): void
    {
        $this->seedDemo();
        config(['jci.acces_membres' => true]);
        $u = User::create(['name' => 'M', 'email' => 'membre@exemple.bj', 'password' => 'MotDePasse2026', 'actif' => true]);
        $u->assignRole('membre');

        $this->post('/connexion', ['email' => 'membre@exemple.bj', 'password' => 'MotDePasse2026'])
            ->assertRedirect(route('dashboard'));
    }

    /* ================= Avatars ================= */

    public function test_avatar_par_defaut_selon_le_sexe(): void
    {
        $f = new Membre(['nom' => 'A', 'prenom' => 'B', 'sexe' => 'F']);
        $h = new Membre(['nom' => 'A', 'prenom' => 'B', 'sexe' => 'M']);
        $this->assertSame('/images/avatars/femme.svg', $f->photo_url);
        $this->assertSame('/images/avatars/homme.svg', $h->photo_url);
        $this->assertFileExists(public_path('images/avatars/femme.svg'));
        $this->assertFileExists(public_path('images/avatars/homme.svg'));

        // Création d'un membre sans photo : acceptée, et l'avatar s'affiche sur sa fiche.
        $this->seedDemo();
        $pres = User::role('president')->first();
        $pres->forceFill(['actif' => true])->save();
        $this->actingAs($pres)->post(route('membres.store'), [
            'nom' => 'Sagbo', 'prenom' => 'Awa', 'sexe' => 'F', 'statut' => 'membre_simple',
        ])->assertSessionHasNoErrors();
        $awa = Membre::where('prenom', 'Awa')->firstOrFail();
        $this->actingAs($pres)->get(route('membres.show', $awa))->assertSee('/images/avatars/femme.svg', false);
    }

    /* ================= Affectation au CDL -> email d'activation ================= */

    public function test_membre_affecte_a_un_poste_recoit_le_lien_et_active_son_compte(): void
    {
        Mail::fake();
        $this->seedDemo();
        $pres = User::role('president')->first();
        $pres->forceFill(['actif' => true])->save();

        $m = Membre::create(['nom' => 'Kora', 'prenom' => 'Ali', 'sexe' => 'M', 'email' => 'ali.kora@exemple.bj', 'statut' => 'membre_simple']);
        $this->actingAs($pres)->post(route('cdl.affecter', Mandat::first()), ['poste' => 'Trésorier Général', 'membre_id' => $m->id])
            ->assertSessionHas('ok', fn ($msg) => str_contains($msg, "email d'activation lui a été envoyé"));

        $jeton = $this->jetonActivation('ali.kora@exemple.bj');
        auth()->logout();

        $this->get(route('activation.show', $jeton))->assertOk();
        $this->post(route('activation.store'), [
            'token' => $jeton, 'password' => 'TresorAli2026', 'password_confirmation' => 'TresorAli2026',
        ])->assertRedirect(route('dashboard'));

        $ali = User::where('email', 'ali.kora@exemple.bj')->firstOrFail();
        $this->assertTrue($ali->actif);
        $this->assertTrue($ali->hasRole('tresorier'));
        $this->viderCache();
        $this->get(route('finances.index'))->assertOk();

        // Le lien ne sert qu'une fois.
        auth()->logout();
        $this->get(route('activation.show', $jeton))->assertSee('Lien invalide');
    }

    /* ================= Changement d'email en libre-service ================= */

    public function test_changement_d_email_avec_confirmation(): void
    {
        Mail::fake();
        $this->seedDemo();
        $tres = User::role('tresorier')->first();
        $tres->forceFill(['actif' => true, 'password' => Hash::make('Tresorier2026')])->save();
        $ancien = $tres->email;

        // Mauvais mot de passe : refusé.
        $this->actingAs($tres)->put(route('profil.email'), ['nouvel_email' => 'neuf@exemple.bj', 'actuel_email' => 'faux'])
            ->assertSessionHasErrors('actuel_email', null, 'email');
        Mail::assertNothingSent();

        // Bonne demande : lien vers la nouvelle adresse, alerte à l'ancienne, email inchangé.
        $this->actingAs($tres)->put(route('profil.email'), ['nouvel_email' => 'neuf@exemple.bj', 'actuel_email' => 'Tresorier2026'])
            ->assertSessionHas('ok');
        $this->assertSame($ancien, $tres->fresh()->email);
        Mail::assertSent(AlerteChangementEmail::class, fn ($m) => $m->hasTo($ancien) && ! $m->confirme);

        $jeton = null;
        Mail::assertSent(ConfirmationEmail::class, function ($m) use (&$jeton) {
            $jeton = $m->token; return $m->hasTo('neuf@exemple.bj');
        });

        // Clic sur le lien (depuis un autre appareil, non connecté).
        auth()->logout();
        $this->get(route('email.confirmer', $jeton))->assertOk()->assertSee('Adresse email confirmée');

        $tres->refresh();
        $this->assertSame('neuf@exemple.bj', $tres->email);
        $this->assertSame('neuf@exemple.bj', $tres->membre->email, 'la fiche membre suit');
        Mail::assertSent(AlerteChangementEmail::class, fn ($m) => $m->hasTo($ancien) && $m->confirme);

        // Lien à usage unique.
        $this->get(route('email.confirmer', $jeton))->assertSee('Lien invalide');
    }

    public function test_lien_de_changement_d_email_expire(): void
    {
        Mail::fake();
        $this->seedDemo();
        $u = User::role('vpm')->first();
        $u->forceFill(['actif' => true, 'password' => Hash::make('VpmMotDePasse1')])->save();
        $this->actingAs($u)->put(route('profil.email'), ['nouvel_email' => 'vpm2@exemple.bj', 'actuel_email' => 'VpmMotDePasse1']);
        $jeton = null;
        Mail::assertSent(ConfirmationEmail::class, function ($m) use (&$jeton) { $jeton = $m->token; return true; });

        $this->travel(25)->hours();
        auth()->logout();
        $this->get(route('email.confirmer', $jeton))->assertSee('Lien invalide');
        $this->assertNotSame('vpm2@exemple.bj', $u->fresh()->email);
    }
}
