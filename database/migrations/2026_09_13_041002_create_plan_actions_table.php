<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('plan_actions', function (Blueprint $t) {
            $t->id();
            $t->string('titre');
            $t->text('description')->nullable();
            $t->string('mois')->nullable();            // format YYYY-MM
            $t->string('statut')->default('a_faire');  // a_faire / en_cours / fait
            $t->foreignId('mandat_id')->nullable()->constrained('mandats')->nullOnDelete();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('plan_actions'); }
};
