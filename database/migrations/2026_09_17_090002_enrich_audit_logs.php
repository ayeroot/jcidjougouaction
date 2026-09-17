<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('audit_logs', function (Blueprint $t) {
            $t->string('role')->nullable()->after('user_id');       // rôle de l'auteur
            $t->json('old_values')->nullable()->after('modifications');
            $t->json('new_values')->nullable()->after('old_values');
        });
    }
    public function down(): void {
        Schema::table('audit_logs', function (Blueprint $t) {
            $t->dropColumn(['role', 'old_values', 'new_values']);
        });
    }
};
