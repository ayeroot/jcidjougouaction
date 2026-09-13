<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('depenses', function (Blueprint $t) {
            $t->id();
            $t->string('libelle');
            $t->decimal('montant', 12, 2)->default(0);
            $t->date('date_depense')->nullable();
            $t->foreignId('projet_id')->nullable()->constrained('projets')->nullOnDelete();
            $t->foreignId('mandat_id')->nullable()->constrained('mandats')->nullOnDelete();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('depenses'); }
};
