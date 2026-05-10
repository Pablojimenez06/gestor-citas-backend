<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'profesional_id',
        'servicio_id',
        'fecha',
        'hora',
        'estado'
    ];

    // Una cita pertenece a un cliente (usuario)
    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    // Una cita pertenece a un profesional
    public function profesional()
    {
        return $this->belongsTo(Profesional::class);
    }

    // Una cita pertenece a un servicio
    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
}