<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('membres', function (Blueprint $t) {
            $t->string('promotion')->nullable()->after('fonction'); // nom de la promotion
        });
    }
    public function down(): void {
        Schema::table('membres', fn (Blueprint $t) => $t->dropColumn('promotion'));
    }
};
