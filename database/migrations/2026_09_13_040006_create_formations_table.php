<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('formations', function (Blueprint $t) {
            $t->id();
            $t->string('titre');
            $t->string('theme')->nullable();
            $t->text('objectifs')->nullable();
            $t->dateTime('date_formation')->nullable();
            $t->string('lieu')->nullable();
            $t->string('statut')->default('planifiee');  // planifiee / realisee / annulee
            $t->foreignId('mandat_id')->nullable()->constrained('mandats')->nullOnDelete();
            $t->foreignId('formateur_id')->nullable()->constrained('formateurs')->nullOnDelete();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('formations'); }
};
