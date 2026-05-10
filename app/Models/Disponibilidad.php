<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disponibilidad extends Model
{
    use HasFactory;

    protected $table = 'disponibilidades'; // añade esta línea

    protected $fillable = [
        'profesional_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin'
    ];

    public function profesional()
    {
        return $this->belongsTo(Profesional::class);
    }
}