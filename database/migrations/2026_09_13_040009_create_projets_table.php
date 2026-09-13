<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('projets', function (Blueprint $t) {
            $t->id();
            $t->string('titre');
            $t->text('description')->nullable();
            $t->string('statut')->default('en_cours'); // en_cours / termine / a_venir
            $t->unsignedTinyInteger('avancement')->default(0); // 0-100
            $t->boolean('public')->default(true);      // visible sur la vitrine
            $t->foreignId('mandat_id')->nullable()->constrained('mandats')->nullOnDelete();
            $t->foreignId('responsable_id')->nullable()->constrained('membres')->nullOnDelete();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('projets'); }
};
