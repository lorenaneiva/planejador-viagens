<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('viagens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('titulo', 200);
            $table->text('descricao')->nullable();
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->string('status', 20);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viagens');
    }
};
