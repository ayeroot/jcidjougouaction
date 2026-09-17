<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\InscriptionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MembreController;
use App\Http\Controllers\PostulantController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\PartenaireController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\EfficaciteController;
use App\Http\Controllers\AnniversaireController;
use App\Http\Controllers\MandatController;
use App\Http\Controllers\HistoriqueController;
use App\Http\Controllers\Admin\UserController;

/* ---------------- Zone publique (vitrine) ---------------- */
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/inscription', [InscriptionController::class, 'create'])->name('inscription.create');
Route::post('/inscription', [InscriptionController::class, 'store'])->name('inscription.store');
Route::get('/inscription/merci', [InscriptionController::class, 'merci'])->name('inscription.merci');

/* ---------------- Authentification ---------------- */
Route::get('/connexion', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::post('/connexion', [LoginController::class, 'login'])->middleware(['guest', 'throttle:6,1']);
Route::post('/deconnexion', [LoginController::class, 'logout'])->name('logout');

/* ---------------- Espace privé ---------------- */
Route::middleware('auth')->prefix('espace')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /* Anniversaires : ouvert à tous les membres connectés */
    Route::get('/anniversaires', [AnniversaireController::class, 'index'])->name('anniversaires.index');

    /* Membres */
    Route::get('/membres', [MembreController::class, 'index'])->name('membres.index');
    Route::get('/membres/{membre}', [MembreController::class, 'show'])->name('membres.show');
    Route::middleware('role:vpm|president')->group(function () {
        Route::get('/membres-nouveau/creer', [MembreController::class, 'create'])->name('membres.create');
        Route::post('/membres', [MembreController::class, 'store'])->name('membres.store');
        Route::get('/membres/{membre}/modifier', [MembreController::class, 'edit'])->name('membres.edit');
        Route::put('/membres/{membre}', [MembreController::class, 'update'])->name('membres.update');
        Route::delete('/membres/{membre}', [MembreController::class, 'destroy'])->name('membres.destroy');
    });

    /* Recrutement */
    Route::middleware('role:vpcd|vpf|president')->group(function () {
        Route::get('/postulants', [PostulantController::class, 'index'])->name('postulants.index');
        Route::get('/postulants/{postulant}', [PostulantController::class, 'show'])->name('postulants.show');
        Route::get('/postulants/{postulant}/releve', [PostulantController::class, 'releve'])->name('postulants.releve');
    });
    Route::middleware('role:vpcd|president')->group(function () {
        Route::patch('/postulants/{postulant}/statut', [PostulantController::class, 'updateStatut'])->name('postulants.statut');
        Route::post('/postulants/{postulant}/convertir', [PostulantController::class, 'convertir'])->name('postulants.convertir');
    });

    /* Formations */
    Route::middleware('role:vpf|president')->group(function () {
        Route::get('/formations', [FormationController::class, 'index'])->name('formations.index');
        Route::get('/formations/creer', [FormationController::class, 'create'])->name('formations.create');
        Route::post('/formations', [FormationController::class, 'store'])->name('formations.store');
        Route::get('/formations/{formation}', [FormationController::class, 'show'])->name('formations.show');
        Route::get('/formations/{formation}/modifier', [FormationController::class, 'edit'])->name('formations.edit');
        Route::put('/formations/{formation}', [FormationController::class, 'update'])->name('formations.update');
        Route::delete('/formations/{formation}', [FormationController::class, 'destroy'])->name('formations.destroy');
        Route::post('/formations/{formation}/pointage', [FormationController::class, 'pointage'])->name('formations.pointage');
        Route::post('/formations/{formation}/rapport', [FormationController::class, 'rapport'])->name('formations.rapport');
        Route::get('/formations/{formation}/liste-presence', [FormationController::class, 'listePresence'])->name('formations.presence');
    });

    /* Finances : consultation Trésorier + Président ; écritures réservées au Trésorier */
    Route::middleware('role:tresorier|president')->group(function () {
        Route::get('/finances', [FinanceController::class, 'index'])->name('finances.index');
        Route::get('/finances/cotisations', [FinanceController::class, 'cotisations'])->name('finances.cotisations');
        Route::get('/finances/contributions', [FinanceController::class, 'contributions'])->name('finances.contributions');
        Route::get('/finances/depenses', [FinanceController::class, 'depenses'])->name('finances.depenses');
    });
    Route::middleware('role:tresorier')->group(function () {
        Route::post('/finances/cotisations', [FinanceController::class, 'storeCotisation'])->name('finances.cotisations.store');
        Route::post('/finances/contributions', [FinanceController::class, 'storeContribution'])->name('finances.contributions.store');
        Route::post('/finances/depenses', [FinanceController::class, 'storeDepense'])->name('finances.depenses.store');
    });

    /* Projets : consultation ouverte, gestion VP Projet / Président */
    Route::get('/projets', [ProjetController::class, 'index'])->name('projets.index');
    Route::get('/projets/{projet}', [ProjetController::class, 'show'])->name('projets.show');
    Route::middleware('role:vp_projet|president')->group(function () {
        Route::get('/projets-nouveau/creer', [ProjetController::class, 'create'])->name('projets.create');
        Route::post('/projets', [ProjetController::class, 'store'])->name('projets.store');
        Route::get('/projets/{projet}/modifier', [ProjetController::class, 'edit'])->name('projets.edit');
        Route::put('/projets/{projet}', [ProjetController::class, 'update'])->name('projets.update');
        Route::delete('/projets/{projet}', [ProjetController::class, 'destroy'])->name('projets.destroy');
    });

    /* Partenaires : VPRE / Président */
    Route::middleware('role:vpre|president')->group(function () {
        Route::get('/partenaires', [PartenaireController::class, 'index'])->name('partenaires.index');
        Route::post('/partenaires', [PartenaireController::class, 'store'])->name('partenaires.store');
        Route::get('/partenaires/{partenaire}/modifier', [PartenaireController::class, 'edit'])->name('partenaires.edit');
        Route::put('/partenaires/{partenaire}', [PartenaireController::class, 'update'])->name('partenaires.update');
        Route::delete('/partenaires/{partenaire}', [PartenaireController::class, 'destroy'])->name('partenaires.destroy');
    });

    /* Archives : Secrétaire Général / Président */
    Route::middleware('role:secretaire|president')->group(function () {
        Route::get('/archives', [ArchiveController::class, 'index'])->name('archives.index');
        Route::post('/archives', [ArchiveController::class, 'store'])->name('archives.store');
        Route::delete('/archives/{archive}', [ArchiveController::class, 'destroy'])->name('archives.destroy');
    });

    /* Plan d'action & Efficacité 100% : VPE / Président */
    Route::middleware('role:vpe|president')->group(function () {
        Route::get('/efficacite', [EfficaciteController::class, 'index'])->name('efficacite.index');
        Route::post('/efficacite/standards', [EfficaciteController::class, 'storeStandard'])->name('efficacite.standards.store');
        Route::patch('/efficacite/standards/{standard}', [EfficaciteController::class, 'toggleStandard'])->name('efficacite.standards.toggle');
        Route::delete('/efficacite/standards/{standard}', [EfficaciteController::class, 'destroyStandard'])->name('efficacite.standards.destroy');
        Route::post('/efficacite/actions', [EfficaciteController::class, 'storeAction'])->name('efficacite.actions.store');
        Route::patch('/efficacite/actions/{action}', [EfficaciteController::class, 'updateActionStatut'])->name('efficacite.actions.update');
        Route::delete('/efficacite/actions/{action}', [EfficaciteController::class, 'destroyAction'])->name('efficacite.actions.destroy');
    });

    /* Mandats : configuration & historique — Président */
    Route::middleware('role:president')->group(function () {
        Route::get('/mandats', [MandatController::class, 'index'])->name('mandats.index');
        Route::get('/mandats/creer', [MandatController::class, 'create'])->name('mandats.create');
        Route::post('/mandats', [MandatController::class, 'store'])->name('mandats.store');
        Route::get('/mandats/{mandat}', [MandatController::class, 'show'])->name('mandats.show');
        Route::get('/mandats/{mandat}/modifier', [MandatController::class, 'edit'])->name('mandats.edit');
        Route::put('/mandats/{mandat}', [MandatController::class, 'update'])->name('mandats.update');
        Route::patch('/mandats/{mandat}/actif', [MandatController::class, 'setActif'])->name('mandats.actif');
    });

    /* Historique des activités (journal d'audit) : Président / VPE */
    Route::middleware('role:president|vpe')->group(function () {
        Route::get('/historique', [HistoriqueController::class, 'index'])->name('historique.index');
    });

    /* Administration des comptes : réservée à l'administrateur.
       L'admin crée les identifiants, attribue les rôles ; chaque compte reçoit un email. */
    Route::middleware('role:admin')->group(function () {
        Route::get('/utilisateurs', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/utilisateurs/creer', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/utilisateurs', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/utilisateurs/{user}/modifier', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/utilisateurs/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::post('/utilisateurs/{user}/reinitialiser', [UserController::class, 'resetPassword'])->name('admin.users.reset');
        Route::delete('/utilisateurs/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    });
});
