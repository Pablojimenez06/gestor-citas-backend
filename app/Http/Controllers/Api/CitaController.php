<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    // GET /api/citas
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            // Admin ve todas las citas
            $citas = Cita::with(['cliente', 'profesional.user', 'servicio'])->get();
        } elseif ($user->role === 'profesional') {
            // Profesional ve solo sus citas
            $citas = Cita::with(['cliente', 'servicio'])
                ->where('profesional_id', $user->profesional->id)
                ->get();
        } else {
            // Cliente ve solo sus citas
            $citas = Cita::with(['profesional.user', 'servicio'])
                ->where('cliente_id', $user->id)
                ->get();
        }

        return response()->json($citas);
    }

    // POST /api/citas
    public function store(Request $request)
{
    $request->validate([
        'profesional_id' => 'required|exists:profesionals,id',
        'servicio_id'    => 'required|exists:servicios,id',
        'fecha'          => 'required|date',
        'hora'           => 'required'
    ]);

    // Comprobar disponibilidad del profesional
    $fecha = new \DateTime($request->fecha);
    $diaSemana = (int)$fecha->format('N'); // 1=Lunes, 7=Domingo

    $disponible = \App\Models\Disponibilidad::where('profesional_id', $request->profesional_id)
        ->where('dia_semana', $diaSemana)
        ->where('hora_inicio', '<=', $request->hora)
        ->where('hora_fin', '>', $request->hora)
        ->exists();

    if (!$disponible) {
        return response()->json([
            'message' => 'El profesional no está disponible en ese día y hora'
        ], 422);
    }

    // Comprobar solapamiento
    $solapamiento = \App\Models\Cita::where('profesional_id', $request->profesional_id)
        ->where('fecha', $request->fecha)
        ->where('hora', $request->hora)
        ->where('estado', '!=', 'cancelada')
        ->exists();

    if ($solapamiento) {
        return response()->json([
            'message' => 'El profesional ya tiene una cita en esa fecha y hora'
        ], 422);
    }

    $cita = \App\Models\Cita::create([
        'cliente_id'     => $request->user()->id,
        'profesional_id' => $request->profesional_id,
        'servicio_id'    => $request->servicio_id,
        'fecha'          => $request->fecha,
        'hora'           => $request->hora,
        'estado'         => 'pendiente'
    ]);

    return response()->json($cita->load(['profesional', 'servicio']), 201);
}

    // GET /api/citas/{id}
    public function show($id)
    {
        $cita = Cita::with(['cliente', 'profesional.user', 'servicio'])->findOrFail($id);
        return response()->json($cita);
    }

    // PUT /api/citas/{id}
    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);

        $request->validate([
            'estado' => 'in:pendiente,confirmada,cancelada'
        ]);

        $cita->update($request->all());
        return response()->json($cita);
    }

    // DELETE /api/citas/{id}
    public function destroy($id)
    {
        Cita::findOrFail($id)->delete();
        return response()->json(['message' => 'Cita eliminada']);
    }

    public function horasOcupadas($profesional_id, $fecha)
{
    $horas = Cita::where('profesional_id', $profesional_id)
        ->where('fecha', $fecha)
        ->where('estado', '!=', 'cancelada')
        ->pluck('hora')
        ->map(fn($h) => substr($h, 0, 5))
        ->toArray();

    return response()->json($horas);
}
}