<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exces')) {
            return;
        }

        Schema::table('exces', function (Blueprint $table) {
            if (!Schema::hasColumn('exces', 'idexce')) {
                if (Schema::hasColumn('exces', 'id')) {
                    $table->renameColumn('id', 'idexce');
                } else {
                    $table->bigIncrements('idexce');
                }
            }

            if (!Schema::hasColumn('exces', 'idcourse')) {
                $table->unsignedBigInteger('idcourse')->nullable()->index();
            }
            if (!Schema::hasColumn('exces', 'aire')) {
                $table->decimal('aire', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('exces', 'maxx')) {
                $table->decimal('maxx', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('exces', 'autorise')) {
                $table->decimal('autorise', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('exces', 'categorie')) {
                $table->string('categorie', 50)->nullable();
            }
            if (!Schema::hasColumn('exces', 'interstation')) {
                $table->string('interstation', 100)->nullable();
            }
            if (!Schema::hasColumn('exces', 'debut')) {
                $table->integer('debut')->nullable();
            }
            if (!Schema::hasColumn('exces', 'fin')) {
                $table->integer('fin')->nullable();
            }
            if (!Schema::hasColumn('exces', 'detail')) {
                $table->string('detail', 100)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Keep repaired data and columns when rolling back application migrations.
    }
};
