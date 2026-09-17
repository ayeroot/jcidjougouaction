<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('depenses', function (Blueprint $t) {
            // projet, prestation, secretariat, fonctionnement, autre
            $t->string('categorie')->default('projet')->after('libelle');
        });
    }
    public function down(): void {
        Schema::table('depenses', fn (Blueprint $t) => $t->dropColumn('categorie'));
    }
};
