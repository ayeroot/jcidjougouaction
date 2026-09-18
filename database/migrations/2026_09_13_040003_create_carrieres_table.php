<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('carrieres', function (Blueprint $t) {
            $t->id();
            $t->foreignId('membre_id')->constrained('membres')->cascadeOnDelete();
            $t->string('type');                 // formation, MC, protocole, ...
            $t->string('annee')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('carrieres'); }
};
