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
        Schema::create('freinage', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->decimal('vitesse', 8, 2)->nullable();
            $table->string('interstation')->nullable();
            $table->text('details')->nullable();
            $table->time('heure')->nullable();
            $table->unsignedBigInteger('idcourse')->nullable();
            
            // Clé étrangère vers la table courses
            $table->foreign('idcourse')->references('idcourse')->on('courses')->onDelete('cascade');
            
            // Index pour améliorer les performances
            $table->index('idcourse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freinage');
    }
};
