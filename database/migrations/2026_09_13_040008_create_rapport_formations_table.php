<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('rapport_formations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('formation_id')->constrained('formations')->cascadeOnDelete();
            $t->text('contenu')->nullable();
            $t->foreignId('redige_par')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('rapport_formations'); }
};
