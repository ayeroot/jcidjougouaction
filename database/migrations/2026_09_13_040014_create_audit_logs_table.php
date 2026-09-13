<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action');                 // created / updated / deleted
            $t->string('auditable_type');
            $t->unsignedBigInteger('auditable_id');
            $t->json('modifications')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('audit_logs'); }
};
