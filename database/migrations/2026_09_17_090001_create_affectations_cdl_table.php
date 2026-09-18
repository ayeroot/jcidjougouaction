<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('affectations_cdl', function (Blueprint $t) {
            $t->id();
            $t->foreignId('mandat_id')->constrained('mandats')->cascadeOnDelete();
            $t->foreignId('membre_id')->constrained('membres')->cascadeOnDelete();
            $t->string('poste');   // ex : "Président Local" (valeur de Membre::FONCTIONS)
            $t->timestamps();
            // Contrainte : un seul membre par poste et par mandat (année).
            $t->unique(['mandat_id', 'poste']);
        });
    }
    public function down(): void { Schema::dropIfExists('affectations_cdl'); }
};
