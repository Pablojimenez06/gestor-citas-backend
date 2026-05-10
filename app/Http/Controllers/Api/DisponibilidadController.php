<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Disponibilidad;
use Illuminate\Http\Request;

class DisponibilidadController extends Controller
{
    // GET /api/disponibilidades/{profesional_id}
    public function getByProfesional($profesional_id)
    {
        $disponibilidades = Disponibilidad::where('profesional_id', $profesional_id)->get();
        return response()->json($disponibilidades);
    }

    // POST /api/disponibilidades
    public function store(Request $request)
    {
        $request->validate([
            'profesional_id' => 'required|exists:profesionals,id',
            'dia_semana'     => 'required|integer|min:1|max:7',
            'hora_inicio'    => 'required',
            'hora_fin'       => 'required'
        ]);

        // Evitar duplicados
        $existe = Disponibilidad::where('profesional_id', $request->profesional_id)
            ->where('dia_semana', $request->dia_semana)
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya existe disponibilidad para ese día'], 422);
        }

        $disponibilidad = Disponibilidad::create($request->all());
        return response()->json($disponibilidad, 201);
    }

    // DELETE /api/disponibilidades/{id}
    public function destroy($id)
    {
        Disponibilidad::findOrFail($id)->delete();
        return response()->json(['message' => 'Disponibilidad eliminada']);
    }
}