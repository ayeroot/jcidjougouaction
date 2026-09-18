<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('postulants', function (Blueprint $t) {
            $t->id();
            $t->string('nom');
            $t->string('prenom');
            $t->date('date_naissance')->nullable();
            $t->string('sexe', 1)->nullable();
            $t->string('email')->nullable();
            $t->string('telephone')->nullable();
            $t->string('ville')->nullable();
            $t->text('motivation')->nullable();
            // statut : nouveau, contacte, en_formation, admis, rejete
            $t->string('statut')->default('nouveau');
            $t->foreignId('membre_id')->nullable()->constrained('membres')->nullOnDelete(); // si admis
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('postulants'); }
};
