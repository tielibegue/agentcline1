<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->string('type', 30);
            $table->string('priorite', 20)->default('MOYENNE');
            $table->string('statut', 40)->default('NOUVELLE');
            $table->string('titre');
            $table->text('description');
            $table->string('application', 100)->nullable();
            $table->string('module_fonctionnel')->nullable();
            $table->string('version_application', 50)->nullable();
            $table->string('environnement')->nullable();
            $table->date('date_incident')->nullable();
            $table->boolean('reproductible')->nullable();
            $table->foreignId('juridiction_id')->nullable()->constrained('juridictions')->nullOnDelete();
            $table->foreignId('declarant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigne_a_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolu_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->timestamp('affecte_le')->nullable();
            $table->timestamp('resolu_le')->nullable();
            $table->timestamp('cloture_le')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['statut', 'priorite']);
            $table->index(['type', 'statut']);
            $table->index('assigne_a_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
