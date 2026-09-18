<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\InscriptionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\PasswordResetController;
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
use App\Http\Controllers\CdlController;
use App\Http\Controllers\HistoriqueController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;

/* ---------------- Zone publique (vitrine) ---------------- */
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/inscription', [InscriptionController::class, 'create'])->name('inscription.create');
Route::post('/inscription', [InscriptionController::class, 'store'])->name('inscription.store');
Route::get('/inscription/merci', [InscriptionController::class, 'merci'])->name('inscription.merci');

/* ---------------- Authentification ---------------- */
Route::get('/connexion', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::post('/connexion', [LoginController::class, 'login'])->middleware(['guest', 'throttle:6,1']);
Route::post('/deconnexion', [LoginController::class, 'logout'])->name('logout');

/* ---------------- Activation de compte (lien email) ---------------- */
Route::middleware('guest')->group(function () {
    Route::get('/activation/{token}', [ActivationController::class, 'show'])->name('activation.show');
    Route::post('/activation', [ActivationController::class, 'store'])->name('activation.store');

    /* Réinitialisation du mot de passe (token sécurisé, à durée limitée) */
    Route::get('/mot-de-passe/oubli', [PasswordResetController::class, 'demande'])->name('password.request');
    Route::post('/mot-de-passe/oubli', [PasswordResetController::class, 'envoyer'])
        ->middleware('throttle:6,1')->name('password.email');
    Route::get('/mot-de-passe/reinitialiser/{token}', [PasswordResetController::class, 'formulaire'])->name('password.reset');
    Route::post('/mot-de-passe/reinitialiser', [PasswordResetController::class, 'reinitialiser'])->name('password.update');
});

/* ---------------- Espace privé ---------------- */
Route::middleware('auth')->prefix('espace')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /* Profil personnel : tout utilisateur connecté (email NON modifiable ici) */
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('/profil/mot-de-passe', [ProfilController::class, 'motDePasse'])->name('profil.password');

    /* Anniversaires : ouvert à tous les membres connectés */
    Route::get('/anniversaires', [AnniversaireController::class, 'index'])->name('anniversaires.index');

    /* Membres : consultation ouverte à tous (dont admin) */
    Route::get('/membres', [MembreController::class, 'index'])->name('membres.index');
    Route::get('/membres/{membre}', [MembreController::class, 'show'])->name('membres.show');
    Route::middleware('permission:membres.gerer')->group(function () {
        Route::get('/membres-nouveau/creer', [MembreController::class, 'create'])->name('membres.create');
        Route::post('/membres', [MembreController::class, 'store'])->name('membres.store');
        Route::get('/membres/{membre}/modifier', [MembreController::class, 'edit'])->name('membres.edit');
        Route::put('/membres/{membre}', [MembreController::class, 'update'])->name('membres.update');
        Route::delete('/membres/{membre}', [MembreController::class, 'destroy'])->name('membres.destroy');
    });

    /* Recrutement : lecture VPCD/VPF/Président (+ admin) ; gestion VPCD/Président */
    Route::middleware('permission:postulants.voir')->group(function () {
        Route::get('/postulants', [PostulantController::class, 'index'])->name('postulants.index');
        Route::get('/postulants/{postulant}', [PostulantController::class, 'show'])->name('postulants.show');
        Route::get('/postulants/{postulant}/releve', [PostulantController::class, 'releve'])->name('postulants.releve');
    });
    Route::middleware('permission:postulants.gerer')->group(function () {
        Route::patch('/postulants/{postulant}/statut', [PostulantController::class, 'updateStatut'])->name('postulants.statut');
        Route::post('/postulants/{postulant}/convertir', [PostulantController::class, 'convertir'])->name('postulants.convertir');
    });

    /* Formations : lecture VPF/Président (+ admin) ; gestion VPF/Président */
    Route::middleware('permission:formations.voir')->group(function () {
        Route::get('/formations', [FormationController::class, 'index'])->name('formations.index');
        Route::get('/formations/{formation}', [FormationController::class, 'show'])->name('formations.show');
        Route::get('/formations/{formation}/liste-presence', [FormationController::class, 'listePresence'])->name('formations.presence');
    });
    Route::middleware('permission:formations.gerer')->group(function () {
        Route::get('/formations-nouvelle/creer', [FormationController::class, 'create'])->name('formations.create');
        Route::post('/formations', [FormationController::class, 'store'])->name('formations.store');
        Route::get('/formations/{formation}/modifier', [FormationController::class, 'edit'])->name('formations.edit');
        Route::put('/formations/{formation}', [FormationController::class, 'update'])->name('formations.update');
        Route::delete('/formations/{formation}', [FormationController::class, 'destroy'])->name('formations.destroy');
        Route::post('/formations/{formation}/pointage', [FormationController::class, 'pointage'])->name('formations.pointage');
        Route::post('/formations/{formation}/rapport', [FormationController::class, 'rapport'])->name('formations.rapport');
    });

    /* Finances : Trésorier + Président uniquement — PAS d'accès administrateur */
    Route::middleware('permission:finances.voir')->group(function () {
        Route::get('/finances', [FinanceController::class, 'index'])->name('finances.index');
        Route::get('/finances/cotisations', [FinanceController::class, 'cotisations'])->name('finances.cotisations');
        Route::get('/finances/contributions', [FinanceController::class, 'contributions'])->name('finances.contributions');
        Route::get('/finances/depenses', [FinanceController::class, 'depenses'])->name('finances.depenses');
    });
    Route::middleware('permission:finances.gerer')->group(function () {
        Route::post('/finances/cotisations', [FinanceController::class, 'storeCotisation'])->name('finances.cotisations.store');
        Route::post('/finances/contributions', [FinanceController::class, 'storeContribution'])->name('finances.contributions.store');
        Route::post('/finances/depenses', [FinanceController::class, 'storeDepense'])->name('finances.depenses.store');
    });

    /* Projets : consultation ouverte (dont admin) ; gestion VP Projet / Président */
    Route::get('/projets', [ProjetController::class, 'index'])->name('projets.index');
    Route::get('/projets/{projet}', [ProjetController::class, 'show'])->name('projets.show');
    Route::middleware('permission:projets.gerer')->group(function () {
        Route::get('/projets-nouveau/creer', [ProjetController::class, 'create'])->name('projets.create');
        Route::post('/projets', [ProjetController::class, 'store'])->name('projets.store');
        Route::get('/projets/{projet}/modifier', [ProjetController::class, 'edit'])->name('projets.edit');
        Route::put('/projets/{projet}', [ProjetController::class, 'update'])->name('projets.update');
        Route::delete('/projets/{projet}', [ProjetController::class, 'destroy'])->name('projets.destroy');
    });

    /* Partenaires : lecture VPRE/Président (+ admin) ; gestion VPRE/Président */
    Route::middleware('permission:partenaires.voir')->group(function () {
        Route::get('/partenaires', [PartenaireController::class, 'index'])->name('partenaires.index');
    });
    Route::middleware('permission:partenaires.gerer')->group(function () {
        Route::post('/partenaires', [PartenaireController::class, 'store'])->name('partenaires.store');
        Route::get('/partenaires/{partenaire}/modifier', [PartenaireController::class, 'edit'])->name('partenaires.edit');
        Route::put('/partenaires/{partenaire}', [PartenaireController::class, 'update'])->name('partenaires.update');
        Route::delete('/partenaires/{partenaire}', [PartenaireController::class, 'destroy'])->name('partenaires.destroy');
    });

    /* Archives : lecture Secrétaire/Président (+ admin) ; gestion Secrétaire/Président */
    Route::middleware('permission:archives.voir')->group(function () {
        Route::get('/archives', [ArchiveController::class, 'index'])->name('archives.index');
    });
    Route::middleware('permission:archives.gerer')->group(function () {
        Route::post('/archives', [ArchiveController::class, 'store'])->name('archives.store');
        Route::delete('/archives/{archive}', [ArchiveController::class, 'destroy'])->name('archives.destroy');
    });

    /* 100% efficacité : lecture VPE/Président (+ admin) ; gestion VPE/Président */
    Route::middleware('permission:efficacite.voir')->group(function () {
        Route::get('/efficacite', [EfficaciteController::class, 'index'])->name('efficacite.index');
    });
    Route::middleware('permission:efficacite.gerer')->group(function () {
        Route::post('/efficacite/standards', [EfficaciteController::class, 'storeStandard'])->name('efficacite.standards.store');
        Route::patch('/efficacite/standards/{standard}', [EfficaciteController::class, 'toggleStandard'])->name('efficacite.standards.toggle');
        Route::delete('/efficacite/standards/{standard}', [EfficaciteController::class, 'destroyStandard'])->name('efficacite.standards.destroy');
        Route::post('/efficacite/actions', [EfficaciteController::class, 'storeAction'])->name('efficacite.actions.store');
        Route::patch('/efficacite/actions/{action}', [EfficaciteController::class, 'updateActionStatut'])->name('efficacite.actions.update');
        Route::delete('/efficacite/actions/{action}', [EfficaciteController::class, 'destroyAction'])->name('efficacite.actions.destroy');
    });

    /* Mandats : lecture Président (+ admin) ; configuration Président uniquement */
    Route::middleware('permission:mandats.voir')->group(function () {
        Route::get('/mandats', [MandatController::class, 'index'])->name('mandats.index');
        Route::get('/mandats/{mandat}', [MandatController::class, 'show'])->name('mandats.show');
    });
    Route::middleware('permission:mandats.gerer')->group(function () {
        Route::get('/mandats-nouveau/creer', [MandatController::class, 'create'])->name('mandats.create');
        Route::post('/mandats', [MandatController::class, 'store'])->name('mandats.store');
        Route::get('/mandats/{mandat}/modifier', [MandatController::class, 'edit'])->name('mandats.edit');
        Route::put('/mandats/{mandat}', [MandatController::class, 'update'])->name('mandats.update');
        Route::patch('/mandats/{mandat}/actif', [MandatController::class, 'setActif'])->name('mandats.actif');
        /* Configuration du CDL (affectation des postes) */
        Route::post('/mandats/{mandat}/cdl', [CdlController::class, 'affecter'])->name('cdl.affecter');
        Route::delete('/mandats/{mandat}/cdl/{affectation}', [CdlController::class, 'retirer'])->name('cdl.retirer');
    });

    /* Historique des activités (audit) : Président / VPE / Admin (lecture) */
    Route::middleware('permission:historique.voir')->group(function () {
        Route::get('/historique', [HistoriqueController::class, 'index'])->name('historique.index');
    });

    /* Administration des comptes : réservée à l'administrateur */
    Route::middleware('permission:utilisateurs.gerer')->group(function () {
        Route::get('/utilisateurs', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/utilisateurs/creer', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/utilisateurs', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/utilisateurs/{user}/modifier', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/utilisateurs/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::post('/utilisateurs/{user}/activation', [UserController::class, 'renvoyerActivation'])->name('admin.users.activation');
        Route::post('/utilisateurs/{user}/reinitialiser', [UserController::class, 'resetPassword'])->name('admin.users.reset');
        Route::post('/utilisateurs/{user}/actif', [UserController::class, 'toggleActif'])->name('admin.users.toggle');
        Route::delete('/utilisateurs/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

        /* Rôles & permissions (administrable dynamiquement) */
        Route::get('/roles', [RoleController::class, 'index'])->name('admin.roles.index');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
        Route::post('/roles/{role}/reset', [RoleController::class, 'reset'])->name('admin.roles.reset');
    });

    /* Site vitrine : gestion du contenu — permission vitrine.gerer */
    Route::middleware('permission:vitrine.gerer')->group(function () {
        Route::get('/vitrine', [\App\Http\Controllers\Admin\VitrineController::class, 'edit'])->name('admin.vitrine.edit');
        Route::put('/vitrine', [\App\Http\Controllers\Admin\VitrineController::class, 'update'])->name('admin.vitrine.update');
    });
});
