<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('favoritos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('destino_id')
                ->constrained('destinos')
                ->cascadeOnDelete();

            $table->timestamp('created_at')->nullable();

            $table->unique(['usuario_id', 'destino_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favoritos');
    }
};
