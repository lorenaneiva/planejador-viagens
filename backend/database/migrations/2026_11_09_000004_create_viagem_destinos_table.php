<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('viagem_destinos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('viagem_id')
                ->constrained('viagens')
                ->cascadeOnDelete();

            $table->foreignId('destino_id')
                ->constrained('destinos')
                ->restrictOnDelete();

            $table->integer('ordem');
            $table->date('data_chegada')->nullable();
            $table->date('data_saida')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viagem_destinos');
    }
};
