<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * M2 — Un email = un seul membre (empêche la collision d'email qui permettait
 * de réécrire le rôle d'un autre compte via l'affectation du CDL).
 */
return new class extends Migration
{
    public function up(): void
    {
        $doublons = DB::table('membres')->whereNotNull('email')
            ->select('email')->groupBy('email')->havingRaw('COUNT(*) > 1')->pluck('email');

        if ($doublons->isNotEmpty()) {
            throw new RuntimeException(
                'Emails en double dans la table membres, à corriger avant la migration : '.$doublons->implode(', ')
            );
        }

        Schema::table('membres', function (Blueprint $t) {
            $t->unique('email');
        });
    }

    public function down(): void
    {
        Schema::table('membres', function (Blueprint $t) {
            $t->dropUnique(['email']);
        });
    }
};
