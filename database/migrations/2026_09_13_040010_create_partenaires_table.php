<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('partenaires', function (Blueprint $t) {
            $t->id();
            $t->string('nom');
            $t->string('type')->nullable();
            $t->string('contact')->nullable();
            $t->text('description')->nullable();   // ← AJOUTER
            $t->string('logo')->nullable();
            $t->boolean('public')->default(true);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('partenaires'); }
};
