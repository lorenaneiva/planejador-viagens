<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViagemDestino extends Model
{
    protected $table = 'viagem_destinos';

    public $timestamps = false;

    protected $fillable = [
        'viagem_id',
        'destino_id',
        'ordem',
        'data_chegada',
        'data_saida',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
            'data_chegada' => 'date',
            'data_saida' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function viagem(): BelongsTo
    {
        return $this->belongsTo(Viagem::class, 'viagem_id');
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(Destino::class, 'destino_id');
    }
}
