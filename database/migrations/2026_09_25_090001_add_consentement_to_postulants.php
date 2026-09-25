<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** M4 — Date du consentement du candidat au traitement de ses données. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulants', function (Blueprint $t) {
            $t->timestamp('consentement_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('postulants', function (Blueprint $t) {
            $t->dropColumn('consentement_at');
        });
    }
};
