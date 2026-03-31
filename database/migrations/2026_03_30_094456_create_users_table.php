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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
    
            // Auth
            $table->string('email')->unique();
            $table->string('password');
            $table->string('username')->unique()->nullable();
            
            // Profil
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('cover_photo')->nullable();
            $table->text('bio')->nullable();
            
            // Type & rôle
            $table->enum('user_type', ['particulier', 'professionnel'])->default('particulier');
            
            // Localisation
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('FR');
            
            // Info pro
            $table->string('company_name')->nullable();
            $table->string('siret')->nullable();
            $table->string('metier')->nullable();
            
            // KYC / Vérification
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->enum('identity_status', ['pending', 'verified', 'rejected', 'manual_review'])->default('pending');
            
            // Statut
            $table->boolean('is_active')->default(true);
            $table->string('locale')->default('fr');
            
            // Timestamps
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
