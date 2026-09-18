<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('mandats', function (Blueprint $t) {
            $t->id();
            $t->string('annee');                 // ex: "2026"
            $t->string('theme')->nullable();     // thème principal
            $t->date('date_debut')->nullable();
            $t->date('date_fin')->nullable();
            $t->boolean('actif')->default(false);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('mandats'); }
};
