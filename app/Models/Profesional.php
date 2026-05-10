<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesional extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'especialidad',
        'telefono'
    ];

    // Un profesional pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Un profesional tiene muchos servicios
    public function servicios()
    {
        return $this->hasMany(Servicio::class);
    }

    // Un profesional tiene muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    public function disponibilidades()
    {
        return $this->hasMany(Disponibilidad::class);
    }
}