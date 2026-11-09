<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('destinos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('estado', 100)->nullable();
            $table->string('pais', 100);
            $table->string('codigo_pais', 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('moeda', 3)->nullable();
            $table->string('osm_id', 50)->nullable();
            $table->string('osm_type', 20)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinos');
    }
};
