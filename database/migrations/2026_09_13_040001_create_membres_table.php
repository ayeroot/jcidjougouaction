<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('membres', function (Blueprint $t) {
            $t->id();
            $t->string('nom');
            $t->string('prenom');
            $t->date('date_naissance')->nullable();
            $t->string('sexe', 1)->nullable();       // M / F
            $t->string('email')->nullable();
            $t->string('telephone')->nullable();
            $t->string('photo')->nullable();
            $t->string('fonction')->nullable();      // fonction dans le bureau
            $t->string('ville')->nullable();
            $t->string('adresse')->nullable();
            // statut : actif, honoraire, past_president, membre_honneur
            $t->string('statut')->default('actif');
            $t->date('date_adhesion')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('membres'); }
};
