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
        Schema::create('journal', function (Blueprint $table) {
            $table->id();
            $table->string('matricule', 50)->nullable();
            $table->string('nom')->nullable();
            $table->date('ladate')->nullable();
            $table->time('heure')->nullable();
            $table->string('action')->nullable();
            $table->text('detail')->nullable();
            $table->string('site', 10)->nullable();
            
            // Index pour améliorer les performances
            $table->index('matricule');
            $table->index('ladate');
            $table->index('site');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal');
    }
};
