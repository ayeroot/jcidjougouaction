<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LogoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed();
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        Storage::fake('public');
    }

    public function test_sans_logo_le_badge_jci_s_affiche(): void
    {
        $this->get('/')->assertOk()->assertSee('>JCI</span>', false);
    }

    public function test_logo_de_la_vitrine_affiche_dans_menus_et_footer(): void
    {
        $admin = User::role('admin')->first();
        $admin->forceFill(['actif' => true])->save();

        $this->actingAs($admin)->put(route('admin.vitrine.update'), [
            'vitrine_logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        ])->assertSessionHasNoErrors();

        $chemin = Setting::get('vitrine_logo');
        $this->assertNotNull($chemin);
        $url = '/storage/'.$chemin;

        // Site vitrine : en-tête + footer
        $accueil = $this->get('/')->assertOk()->getContent();
        $this->assertSame(2, substr_count($accueil, 'src="'.$url.'"'), 'logo attendu dans le menu ET le footer');

        // Dashboard : barre latérale
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('src="'.$url.'"', false);

        // Page de connexion
        auth()->logout();
        $this->get(route('login'))->assertOk()->assertSee('src="'.$url.'"', false);
    }
}
