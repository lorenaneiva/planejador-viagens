<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Viagem extends Model
{
    protected $table = 'viagens';

    protected $fillable = [
        'usuario_id',
        'titulo',
        'descricao',
        'data_inicio',
        'data_fim',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function destinos(): BelongsToMany
    {
        return $this->belongsToMany(
            Destino::class,
            'viagem_destinos',
            'viagem_id',
            'destino_id'
        )->withPivot([
            'id',
            'ordem',
            'data_chegada',
            'data_saida',
            'observacoes',
            'created_at',
        ])->orderByPivot('ordem');
    }

    public function viagemDestinos(): HasMany
    {
        return $this->hasMany(ViagemDestino::class, 'viagem_id');
    }
}
