<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('actif')->default(true)->after('password');
            $t->string('activation_token')->nullable()->after('actif');
            $t->timestamp('activation_expire_at')->nullable()->after('activation_token');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['actif', 'activation_token', 'activation_expire_at']);
        });
    }
};
