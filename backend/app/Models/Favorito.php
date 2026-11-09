<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorito extends Model
{
    protected $table = 'favoritos';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'destino_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(Destino::class, 'destino_id');
    }
}
