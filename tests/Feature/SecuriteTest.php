<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Cotisation;
use App\Models\Mandat;
use App\Models\Membre;
use App\Models\Postulant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Tests de non-régression des failles de l'audit de sécurité.
 * Chaque test rejoue l'attaque : il doit ÉCHOUER si la faille réapparaît.
 */
class SecuriteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Données de démo semées à CHAQUE test (dans la transaction du test).
     * « protected $seed = true » ne suffit pas : si un autre test a déjà migré la
     * base sans seed, Laravel ne relance pas le seed.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed();
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function compte(string $role): User
    {
        $u = User::role($role)->firstOrFail();
        $u->forceFill(['actif' => true])->save();
        return $u;
    }

    private function admin(): User
    {
        return $this->compte('admin');
    }

    private function viderCachePermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /* ---------- C2 : pas de mot de passe par défaut ---------- */

    public function test_c2_aucun_compte_seede_avec_password(): void
    {
        foreach (User::all() as $u) {
            $this->assertFalse(Hash::check('password', $u->password), "{$u->email} utilise 'password'");
        }
    }

    /* ---------- E1 / E2 : comptes suspendus ---------- */

    public function test_e1_compte_suspendu_est_deconnecte(): void
    {
        $u = $this->compte('vpm');
        $this->actingAs($u);
        $u->forceFill(['actif' => false])->save();

        $this->get(route('membres.create'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_e1_suspension_invalide_le_cookie_se_souvenir_de_moi(): void
    {
        $u = $this->compte('vpm');
        $u->forceFill(['remember_token' => 'ancien-token'])->save();

        $this->actingAs($this->admin())->post(route('admin.users.toggle', $u));

        $this->assertNotSame('ancien-token', $u->fresh()->remember_token);
    }

    public function test_e2_reset_ne_reactive_pas_un_compte_suspendu(): void
    {
        Notification::fake();
        $u = $this->compte('tresorier');
        $token = Password::createToken($u);
        $u->forceFill(['actif' => false])->save();

        $this->post(route('password.email'), ['email' => $u->email]);
        Notification::assertNothingSentTo($u);

        $this->post(route('password.update'), [
            'token' => $token, 'email' => $u->email,
            'password' => 'NouveauMotDePasse1', 'password_confirmation' => 'NouveauMotDePasse1',
        ]);
        $this->assertFalse($u->fresh()->actif);
    }

    /* ---------- E3 : liens d'email basés sur APP_URL ---------- */

    public function test_e3_lien_de_reset_ignore_l_entete_host(): void
    {
        config(['app.url' => 'https://jci.example.bj']);
        (new \App\Providers\AppServiceProvider($this->app))->boot(); // ré-applique la config d'URL
        Notification::fake();
        $u = $this->compte('tresorier');

        $this->post('http://attaquant.example/mot-de-passe/oubli', ['email' => $u->email]);

        Notification::assertSentTo($u, \App\Notifications\ReinitialiserMotDePasse::class, function ($n) use ($u) {
            $url = $n->toMail($u)->viewData['url'];
            $this->assertStringStartsWith('https://jci.example.bj/', $url);
            return true;
        });
    }

    /* ---------- M1 : séparation des pouvoirs admin / finances ---------- */

    public function test_m1_admin_ne_peut_pas_s_octroyer_les_finances_via_son_role(): void
    {
        $admin = $this->admin();
        $role = Role::findByName('admin');
        $perms = array_merge($role->permissions->pluck('name')->all(), ['finances.voir', 'finances.gerer']);

        $this->actingAs($admin)->put(route('admin.roles.update', $role), ['permissions' => $perms]);
        $this->viderCachePermissions();

        $this->assertFalse($admin->fresh()->can('finances.voir'));
        $this->actingAs($admin->fresh())->get(route('finances.index'))->assertForbidden();
    }

    public function test_m1_finances_jamais_en_permission_directe(): void
    {
        $vpm = $this->compte('vpm');
        $this->actingAs($this->admin())->put(route('admin.users.update', $vpm), [
            'email' => $vpm->email, 'role' => 'vpm', 'permissions' => ['finances.gerer', 'archives.voir'],
        ]);
        $this->viderCachePermissions();

        $this->assertFalse($vpm->fresh()->can('finances.gerer'));
        $this->assertTrue($vpm->fresh()->can('archives.voir'));
    }

    public function test_m1_admin_ne_peut_pas_nommer_un_tresorier_hors_cdl(): void
    {
        $vpm = $this->compte('vpm');
        $this->actingAs($this->admin())->put(route('admin.users.update', $vpm), [
            'email' => $vpm->email, 'role' => 'tresorier',
        ])->assertSessionHasErrors('role');

        $this->assertFalse($vpm->fresh()->hasRole('tresorier'));
    }

    public function test_m1_admin_ne_peut_pas_changer_l_email_du_president(): void
    {
        $pres = $this->compte('president');
        $avant = $pres->email;

        $this->actingAs($this->admin())->put(route('admin.users.update', $pres), [
            'email' => 'pirate@example.com', 'role' => 'president',
        ])->assertSessionHasErrors('email');

        $this->assertSame($avant, $pres->fresh()->email);
    }

    public function test_m1_changement_d_email_previent_l_ancienne_adresse(): void
    {
        Mail::fake();
        $vpm = $this->compte('vpm');

        $this->actingAs($this->admin())->put(route('admin.users.update', $vpm), [
            'email' => 'nouveau.vpm@example.bj', 'role' => 'vpm',
        ]);

        $this->assertSame('nouveau.vpm@example.bj', $vpm->fresh()->email);
        $this->assertDatabaseHas('audit_logs', ['action' => 'EMAIL_CHANGE', 'auditable_id' => $vpm->id]);
    }

    /* ---------- M2 : collision d'email via le CDL ---------- */

    public function test_m2_president_ne_peut_pas_reecrire_le_role_de_l_admin(): void
    {
        Mail::fake();
        $this->actingAs($this->compte('president'));

        // 1) la fiche membre refuse un email déjà porté par un autre compte
        $this->post(route('membres.store'), [
            'nom' => 'X', 'prenom' => 'Y', 'email' => 'admin@jcidjougou.bj', 'statut' => 'membre_simple',
            'photo' => \Illuminate\Http\UploadedFile::fake()->image('p.jpg'),
        ])->assertSessionHasErrors('email');

        // 2) même si la base contient déjà un tel membre, le CDL ne touche pas au compte admin
        $m = Membre::create(['nom' => 'X', 'prenom' => 'Y', 'email' => 'admin@jcidjougou.bj', 'statut' => 'membre_simple']);
        $this->post(route('cdl.affecter', Mandat::first()), ['poste' => 'Secrétaire Général', 'membre_id' => $m->id]);

        $admin = User::where('email', 'admin@jcidjougou.bj')->first();
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertFalse($admin->hasRole('secretaire'));
    }

    /* ---------- M3 : historique et finances ---------- */

    public function test_m3_historique_masque_les_finances_sans_permission(): void
    {
        $this->travel(1)->minutes(); // l'opération devient la plus récente du journal
        $this->actingAs($this->compte('tresorier'));
        Cotisation::create(['membre_id' => Membre::first()->id, 'montant' => 987654, 'date_cotisation' => now()]);

        $this->actingAs($this->admin())->get(route('historique.index'))
            ->assertOk()->assertDontSee('Cotisation #');
        $this->actingAs($this->compte('president'))->get(route('historique.index'))
            ->assertOk()->assertSee('Cotisation #');
    }

    /* ---------- M4 : formulaire public ---------- */

    private function candidature(array $extra = []): array
    {
        return array_merge(['nom' => 'Test', 'prenom' => 'Candidat', 'telephone' => '0100000000', 'consentement' => '1'], $extra);
    }

    public function test_m4_consentement_obligatoire(): void
    {
        $this->post(route('inscription.store'), $this->candidature(['consentement' => null]))
            ->assertSessionHasErrors('consentement');
    }

    public function test_m4_pot_de_miel_bloque_les_robots(): void
    {
        $avant = Postulant::count();
        $this->post(route('inscription.store'), $this->candidature(['site_web' => 'http://spam.example']))
            ->assertRedirect(route('inscription.merci'));
        $this->assertSame($avant, Postulant::count());
    }

    public function test_m4_limitation_du_nombre_de_candidatures(): void
    {
        $statuts = [];
        for ($i = 0; $i < 12; $i++) {
            $statuts[] = $this->post(route('inscription.store'), $this->candidature(['nom' => "N$i"]))->status();
        }
        $this->assertContains(429, $statuts);
        $this->assertNotNull(Postulant::latest('id')->first()->consentement_at);
    }

    /* ---------- M6 / M7 : pas de script tiers, en-têtes de sécurité ---------- */

    public function test_m6_m7_entetes_et_aucun_cdn(): void
    {
        $r = $this->get('/');
        $r->assertOk()->assertDontSee('cdn.tailwindcss.com', false);

        $this->assertSame('DENY', $r->headers->get('X-Frame-Options'));
        $this->assertSame('nosniff', $r->headers->get('X-Content-Type-Options'));
        $this->assertNotNull($r->headers->get('Referrer-Policy'));
        $this->assertStringContainsString("default-src 'self'", $r->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("frame-ancestors 'none'", $r->headers->get('Content-Security-Policy'));
    }

    /* ---------- Failles faibles ---------- */

    public function test_faible_pas_d_enumeration_des_comptes_desactives(): void
    {
        $u = $this->compte('vpm');
        $u->forceFill(['actif' => false])->save();

        $this->from(route('login'))->post('/connexion', ['email' => $u->email, 'password' => 'mauvais'])
            ->assertSessionHasErrors(['email' => 'Identifiants incorrects.']);
    }

    public function test_faible_couleur_du_mandat_hexadecimale(): void
    {
        $this->actingAs($this->compte('president'))
            ->put(route('mandats.update', Mandat::first()), ['annee' => '2026', 'couleur' => 'red;background:url(//x)'])
            ->assertSessionHasErrors('couleur');
    }

    public function test_faible_mot_de_passe_trop_faible_refuse(): void
    {
        $u = $this->compte('vpm');
        $u->forceFill(['password' => Hash::make('AncienMotDePasse1')])->save();

        $this->actingAs($u)->put(route('profil.password'), [
            'actuel' => 'AncienMotDePasse1', 'password' => 'abc12345', 'password_confirmation' => 'abc12345',
        ])->assertSessionHasErrors('password');
    }

    public function test_journal_des_connexions(): void
    {
        $u = $this->compte('vpm');
        $u->forceFill(['password' => Hash::make('MotDePasseSolide1')])->save();

        $this->post('/connexion', ['email' => $u->email, 'password' => 'MotDePasseSolide1']);

        $this->assertTrue(AuditLog::where('action', 'LOGIN')->where('auditable_id', $u->id)->exists());
    }
}
