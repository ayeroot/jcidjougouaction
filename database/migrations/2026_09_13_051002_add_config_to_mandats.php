<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('mandats', function (Blueprint $t) {
            $t->string('logo')->nullable()->after('theme');
            $t->string('couleur')->nullable()->after('logo');       // couleur du mandat (hex)
            $t->string('photo_famille')->nullable()->after('couleur'); // photo de famille du CDL
        });
    }
    public function down(): void {
        Schema::table('mandats', fn (Blueprint $t) => $t->dropColumn(['logo', 'couleur', 'photo_famille']));
    }
};
