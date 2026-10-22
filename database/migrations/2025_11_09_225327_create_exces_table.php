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
        Schema::create('exces', function (Blueprint $table) {
            $table->bigIncrements('idexce');
            $table->unsignedBigInteger('idcourse')->index();
            $table->decimal('aire', 12, 2)->nullable();
            $table->decimal('maxx', 8, 2)->nullable();
            $table->decimal('autorise', 8, 2)->nullable();
            $table->string('categorie', 50)->nullable();
            $table->string('interstation', 100)->nullable();
            $table->integer('debut')->nullable();
            $table->integer('fin')->nullable();
            $table->string('detail', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exces');
    }
};
