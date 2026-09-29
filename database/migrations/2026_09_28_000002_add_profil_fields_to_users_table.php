<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('JURIDICTION');
            $table->foreignId('juridiction_id')->nullable()->constrained('juridictions')->nullOnDelete();
            $table->string('fonction', 100)->nullable();
            $table->string('telephone', 30)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamp('dernier_acces_le')->nullable();

            $table->index(['role', 'actif']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('juridiction_id');
            $table->dropIndex(['role', 'actif']);
            $table->dropColumn(['role', 'fonction', 'telephone', 'actif', 'dernier_acces_le']);
        });
    }
};
