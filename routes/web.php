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

/* ---------------- Zone publique (vitrine) ---------------- */
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/inscription', [InscriptionController::class, 'create'])->name('inscription.create');
Route::post('/inscription', [InscriptionController::class, 'store'])->name('inscription.store');
Route::get('/inscription/merci', [InscriptionController::class, 'merci'])->name('inscription.merci');

/* ---------------- Authentification ---------------- */
Route::get('/connexion', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::post('/connexion', [LoginController::class, 'login'])->middleware('guest');
Route::post('/deconnexion', [LoginController::class, 'logout'])->name('logout');

/* ---------------- Espace privé ---------------- */
Route::middleware('auth')->prefix('espace')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /* Membres : consultation ouverte à tous les membres connectés */
    Route::get('/membres', [MembreController::class, 'index'])->name('membres.index');
    Route::get('/membres/{membre}', [MembreController::class, 'show'])->name('membres.show');
    Route::middleware('role:vpm|president')->group(function () {
        Route::get('/membres-nouveau/creer', [MembreController::class, 'create'])->name('membres.create');
        Route::post('/membres', [MembreController::class, 'store'])->name('membres.store');
        Route::get('/membres/{membre}/modifier', [MembreController::class, 'edit'])->name('membres.edit');
        Route::put('/membres/{membre}', [MembreController::class, 'update'])->name('membres.update');
        Route::delete('/membres/{membre}', [MembreController::class, 'destroy'])->name('membres.destroy');
    });

    /* Recrutement : VPCD / VPF / Président (consultation) */
    Route::middleware('role:vpcd|vpf|president')->group(function () {
        Route::get('/postulants', [PostulantController::class, 'index'])->name('postulants.index');
        Route::get('/postulants/{postulant}', [PostulantController::class, 'show'])->name('postulants.show');
        Route::get('/postulants/{postulant}/releve', [PostulantController::class, 'releve'])->name('postulants.releve');
    });
    Route::middleware('role:vpcd|president')->group(function () {
        Route::patch('/postulants/{postulant}/statut', [PostulantController::class, 'updateStatut'])->name('postulants.statut');
        Route::post('/postulants/{postulant}/convertir', [PostulantController::class, 'convertir'])->name('postulants.convertir');
    });

    /* Formations : VPF / Président */
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

    /* Finances : Trésorier / Président */
    Route::middleware('role:tresorier|president')->group(function () {
        Route::get('/finances', [FinanceController::class, 'index'])->name('finances.index');
        Route::get('/finances/cotisations', [FinanceController::class, 'cotisations'])->name('finances.cotisations');
        Route::post('/finances/cotisations', [FinanceController::class, 'storeCotisation'])->name('finances.cotisations.store');
        Route::get('/finances/contributions', [FinanceController::class, 'contributions'])->name('finances.contributions');
        Route::post('/finances/contributions', [FinanceController::class, 'storeContribution'])->name('finances.contributions.store');
        Route::get('/finances/depenses', [FinanceController::class, 'depenses'])->name('finances.depenses');
        Route::post('/finances/depenses', [FinanceController::class, 'storeDepense'])->name('finances.depenses.store');
    });
});
