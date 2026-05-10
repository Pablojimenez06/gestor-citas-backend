<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'profesional_id',
        'nombre',
        'descripcion',
        'duracion',
        'precio'
    ];

    // Un servicio pertenece a un profesional
    public function profesional()
    {
        return $this->belongsTo(Profesional::class);
    }

    // Un servicio tiene muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}