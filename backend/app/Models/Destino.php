<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destino extends Model
{
    protected $table = 'destinos';

    public $timestamps = false;

    protected $fillable = [
        'nome',
        'estado',
        'pais',
        'codigo_pais',
        'latitude',
        'longitude',
        'moeda',
        'osm_id',
        'osm_type',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'created_at' => 'datetime',
        ];
    }

    public function favoritos(): HasMany
    {
        return $this->hasMany(Favorito::class, 'destino_id');
    }

    public function viagens(): BelongsToMany
    {
        return $this->belongsToMany(
            Viagem::class,
            'viagem_destinos',
            'destino_id',
            'viagem_id'
        )->withPivot([
            'id',
            'ordem',
            'data_chegada',
            'data_saida',
            'observacoes',
            'created_at',
        ]);
    }
}
