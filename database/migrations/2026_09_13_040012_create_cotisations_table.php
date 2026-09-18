<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('cotisations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('membre_id')->constrained('membres')->cascadeOnDelete();
            $t->foreignId('mandat_id')->nullable()->constrained('mandats')->nullOnDelete();
            $t->decimal('montant', 12, 2)->default(0);
            $t->date('date_cotisation')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('cotisations'); }
};
