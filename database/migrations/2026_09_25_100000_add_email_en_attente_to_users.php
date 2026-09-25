<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Changement d'email en libre-service : nouvelle adresse en attente de confirmation. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('email_en_attente')->nullable();
            $t->string('email_token', 64)->nullable()->index(); // haché (sha256)
            $t->timestamp('email_token_expire_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['email_en_attente', 'email_token', 'email_token_expire_at']);
        });
    }
};
