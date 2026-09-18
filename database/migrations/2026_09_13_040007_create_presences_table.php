<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('presences', function (Blueprint $t) {
            $t->id();
            $t->foreignId('formation_id')->constrained('formations')->cascadeOnDelete();
            $t->foreignId('postulant_id')->constrained('postulants')->cascadeOnDelete();
            $t->boolean('present')->default(false);
            $t->timestamps();
            $t->unique(['formation_id', 'postulant_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('presences'); }
};
