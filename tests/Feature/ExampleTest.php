<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    // Exécute les migrations sur la base de test (sqlite :memory:) avant chaque test.
    use RefreshDatabase;

    public function test_la_vitrine_repond(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_la_page_de_connexion_repond(): void
    {
        $this->get('/connexion')->assertStatus(200);
    }

    public function test_lespace_prive_redirige_les_invites(): void
    {
        $this->get('/espace')->assertRedirect('/connexion');
    }

    public function test_la_page_mot_de_passe_oublie_repond(): void
    {
        $this->get('/mot-de-passe/oubli')->assertStatus(200);
    }
}
