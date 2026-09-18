<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('archives', function (Blueprint $t) {
            $t->id();
            $t->string('titre');
            $t->string('type')->default('document'); // rapport / photo / document / autre
            $t->text('description')->nullable();
            $t->string('reference')->nullable();      // lien ou cote de classement
            $t->date('date_document')->nullable();
            $t->foreignId('mandat_id')->nullable()->constrained('mandats')->nullOnDelete();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('archives'); }
};
