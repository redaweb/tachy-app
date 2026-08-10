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
        // Vérifier si la table users existe et a les bonnes colonnes
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Ajouter les colonnes manquantes si elles n'existent pas
                if (!Schema::hasColumn('users', 'nom')) {
                    $table->string('nom')->nullable()->after('id');
                }
                if (!Schema::hasColumn('users', 'motpass')) {
                    $table->string('motpass')->nullable()->after('nom');
                }
                if (!Schema::hasColumn('users', 'profil')) {
                    $table->string('profil')->default('user')->after('motpass');
                }
                if (!Schema::hasColumn('users', 'site')) {
                    $table->string('site')->nullable()->after('profil');
                }
                if (!Schema::hasColumn('users', 'envBloque')) {
                    $table->boolean('envBloque')->default(false)->after('site');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nom')) {
                $table->dropColumn('nom');
            }
            if (Schema::hasColumn('users', 'motpass')) {
                $table->dropColumn('motpass');
            }
            if (Schema::hasColumn('users', 'profil')) {
                $table->dropColumn('profil');
            }
            if (Schema::hasColumn('users', 'site')) {
                $table->dropColumn('site');
            }
            if (Schema::hasColumn('users', 'envBloque')) {
                $table->dropColumn('envBloque');
            }
        });
    }
};
