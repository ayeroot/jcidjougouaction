<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('standards', function (Blueprint $t) {
            $t->id();
            $t->string('libelle');
            $t->string('categorie')->nullable();
            $t->boolean('atteint')->default(false);
            $t->foreignId('mandat_id')->nullable()->constrained('mandats')->nullOnDelete();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('standards'); }
};
