<?php

namespace App\GraphQL;

use App\Models\Destino;
use App\Models\Favorito;
use App\Models\Viagem;
use App\Models\ViagemDestino;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Api
{
    private function usuarioId(): int
    {
        return (int) Auth::guard('sanctum')->id();
    }

    private function validar(array $dados, array $regras): array
    {
        // O Lighthouse transforma o scalar Date em objeto de data.
        // Normalizamos antes de aplicar date_format:Y-m-d.
        foreach ([
            'data_inicio',
            'data_fim',
            'data_chegada',
            'data_saida',
        ] as $campo) {
            if (
                isset($dados[$campo]) &&
                $dados[$campo] instanceof \DateTimeInterface
            ) {
                $dados[$campo] = $dados[$campo]->format('Y-m-d');
            }
        }

        return Validator::make($dados, $regras)->validate();
    }

    private function minhaViagem(int|string $id): Viagem
    {
        return Viagem::where('usuario_id', $this->usuarioId())
            ->findOrFail($id);
    }

    private function minhaParada(int|string $id): ViagemDestino
    {
        return ViagemDestino::whereHas(
            'viagem',
            fn ($q) => $q->where('usuario_id', $this->usuarioId())
        )->findOrFail($id);
    }

    private function validarDatas(string $inicio, string $fim): void
    {
        if ($fim < $inicio) {
            throw ValidationException::withMessages([
                'data_fim' => ['A data final deve ser igual ou posterior à inicial.'],
            ]);
        }
    }

    // CONSULTAS

    public function minhasViagens(): mixed
    {
        return Viagem::where('usuario_id', $this->usuarioId())
            ->orderByDesc('created_at')
            ->get();
    }

    public function viagem($_, array $args): ?Viagem
    {
        return Viagem::where('usuario_id', $this->usuarioId())
            ->find($args['id']);
    }

    public function meusFavoritos(): mixed
    {
        return Favorito::where('usuario_id', $this->usuarioId())
            ->orderByDesc('id')
            ->get();
    }

    // DESTINOS

    public function criarDestino($_, array $args): Destino
    {
        $dados = $this->validar($args, [
            'nome' => 'required|string|max:150',
            'estado' => 'nullable|string|max:100',
            'pais' => 'required|string|max:100',
            'codigo_pais' => 'nullable|string|size:2',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'moeda' => 'nullable|string|size:3',
            'osm_id' => 'nullable|string|max:50',
            'osm_type' => 'nullable|string|max:20',
        ]);

        $dados['created_at'] = now();

        return Destino::create($dados);
    }

    // VIAGENS

    public function criarViagem($_, array $args): Viagem
    {
        $dados = $this->validar($args, [
            'titulo' => 'required|string|max:200',
            'descricao' => 'nullable|string',
            'data_inicio' => 'required|date_format:Y-m-d',
            'data_fim' => 'required|date_format:Y-m-d',
            'status' => 'required|string|max:20',
        ]);

        $this->validarDatas(
            $dados['data_inicio'],
            $dados['data_fim']
        );

        $dados['usuario_id'] = $this->usuarioId();

        return Viagem::create($dados);
    }

    public function atualizarViagem($_, array $args): Viagem
    {
        $viagem = $this->minhaViagem($args['id']);

        unset($args['id']);

        $dados = $this->validar($args, [
            'titulo' => 'sometimes|required|string|max:200',
            'descricao' => 'nullable|string',
            'data_inicio' => 'sometimes|required|date_format:Y-m-d',
            'data_fim' => 'sometimes|required|date_format:Y-m-d',
            'status' => 'sometimes|required|string|max:20',
        ]);

        $inicio = $dados['data_inicio']
            ?? $viagem->data_inicio->format('Y-m-d');

        $fim = $dados['data_fim']
            ?? $viagem->data_fim->format('Y-m-d');

        $this->validarDatas($inicio, $fim);

        $viagem->update($dados);

        return $viagem->refresh();
    }

    public function excluirViagem($_, array $args): bool
    {
        return (bool) DB::transaction(function () use ($args) {
            return $this->minhaViagem($args['id'])->delete();
        });
    }

    // FAVORITOS

    public function adicionarFavorito($_, array $args): Favorito
    {
        Destino::findOrFail($args['destino_id']);

        return Favorito::firstOrCreate(
            [
                'usuario_id' => $this->usuarioId(),
                'destino_id' => $args['destino_id'],
            ],
            [
                'created_at' => now(),
            ]
        );
    }

    public function removerFavorito($_, array $args): bool
    {
        return Favorito::where('usuario_id', $this->usuarioId())
            ->where('destino_id', $args['destino_id'])
            ->delete() > 0;
    }

    // DESTINOS DAS VIAGENS

    public function adicionarDestinoViagem($_, array $args): ViagemDestino
    {
        $viagem = $this->minhaViagem($args['viagem_id']);

        Destino::findOrFail($args['destino_id']);

        $dados = $this->validar($args, [
            'viagem_id' => 'required|integer',
            'destino_id' => 'required|integer',
            'ordem' => 'required|integer|min:1',
            'data_chegada' => 'nullable|date_format:Y-m-d',
            'data_saida' => 'nullable|date_format:Y-m-d',
            'observacoes' => 'nullable|string',
        ]);

        $inicio = $dados['data_chegada']
            ?? $viagem->data_inicio->format('Y-m-d');

        $fim = $dados['data_saida']
            ?? $viagem->data_fim->format('Y-m-d');

        $this->validarDatas($inicio, $fim);

        return $viagem->viagemDestinos()->create([
            'destino_id' => $dados['destino_id'],
            'ordem' => $dados['ordem'],
            'data_chegada' => $dados['data_chegada'] ?? null,
            'data_saida' => $dados['data_saida'] ?? null,
            'observacoes' => $dados['observacoes'] ?? null,
        ]);
    }

    public function atualizarDestinoViagem($_, array $args): ViagemDestino
    {
        $parada = $this->minhaParada($args['id']);

        unset($args['id']);

        $dados = $this->validar($args, [
            'ordem' => 'sometimes|required|integer|min:1',
            'data_chegada' => 'nullable|date_format:Y-m-d',
            'data_saida' => 'nullable|date_format:Y-m-d',
            'observacoes' => 'nullable|string',
        ]);

        $inicio = array_key_exists('data_chegada', $dados)
            ? $dados['data_chegada']
            : $parada->data_chegada?->format('Y-m-d');

        $fim = array_key_exists('data_saida', $dados)
            ? $dados['data_saida']
            : $parada->data_saida?->format('Y-m-d');

        if ($inicio && $fim) {
            $this->validarDatas($inicio, $fim);
        }

        $parada->update($dados);

        return $parada->refresh();
    }

    public function removerDestinoViagem($_, array $args): bool
    {
        return (bool) $this->minhaParada($args['id'])->delete();
    }
}
