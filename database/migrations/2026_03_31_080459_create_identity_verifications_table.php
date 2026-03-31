<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('identity_verifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Documents soumis
            $table->string('selfie_url')->nullable();
            $table->string('id_document_url')->nullable();
            $table->enum('id_document_type', ['passport', 'id_card', 'driver_license'])->nullable();

            // Statut — basé sur Emergent
            $table->enum('status', ['pending', 'processing', 'verified', 'rejected', 'manual_review'])->default('pending');

            // Résultat IA
            $table->float('ai_confidence_score')->nullable();
            $table->text('ai_analysis')->nullable();

            // Validation manuelle (backup si pas de clé IA)
            $table->foreignId('manual_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('manual_review_notes')->nullable();
            $table->timestamp('manual_review_at')->nullable();

            // Rejet
            $table->string('rejection_reason')->nullable();

            // Dates
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identity_verifications');
    }
};
