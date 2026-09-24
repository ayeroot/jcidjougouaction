<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
                Schema::create('contributions', function (Blueprint $t) {
            $t->id();
            // participation_membre, contribution_membre, don_particulier, don_partenaire, autre
            $t->string('type')->default('don_particulier');
            $t->string('source')->nullable();          // note / précision libre
            $t->string('donateur')->nullable();        // nom du donateur (don particulier)
            $t->decimal('montant', 12, 2)->default(0);
            $t->date('date_contribution')->nullable();
            $t->foreignId('membre_id')->nullable()->constrained('membres')->nullOnDelete();
            $t->foreignId('partenaire_id')->nullable()->constrained('partenaires')->nullOnDelete();
            $t->foreignId('projet_id')->nullable()->constrained('projets')->nullOnDelete(); // null = global
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('contributions'); }
};
