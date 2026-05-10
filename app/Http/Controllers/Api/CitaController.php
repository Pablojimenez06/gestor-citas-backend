<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    /**
     * GET /api/citas
     * Devuelve las citas según el rol del usuario autenticado.
     * Sanctum inyecta automáticamente el usuario a través del token Bearer.
     */
    public function index(Request $request)
    {
        // Obtenemos el usuario autenticado a partir del token de la petición
        $user = $request->user();

        if ($user->role === 'admin') {
            // El admin ve todas las citas del sistema
            // with() carga las relaciones para evitar N+1 queries (una sola consulta con JOIN)
            $citas = Cita::with(['cliente', 'profesional.user', 'servicio'])->get();

        } elseif ($user->role === 'profesional') {
            // El profesional solo ve las citas asignadas a su perfil
            // $user->profesional accede a la relación hasOne definida en el modelo User
            $citas = Cita::with(['cliente', 'servicio'])
                ->where('profesional_id', $user->profesional->id)
                ->get();

        } else {
            // El cliente solo ve sus propias citas
            $citas = Cita::with(['profesional.user', 'servicio'])
                ->where('cliente_id', $user->id)
                ->get();
        }

        // Devolvemos las citas en formato JSON con código HTTP 200
        return response()->json($citas);
    }

    /**
     * POST /api/citas
     * Crea una nueva cita tras validar disponibilidad y solapamiento.
     */
    public function store(Request $request)
    {
        // Validamos que los campos requeridos existan y sean correctos
        // exists:profesionals,id comprueba que el profesional exista en la BD
        $request->validate([
            'profesional_id' => 'required|exists:profesionals,id',
            'servicio_id'    => 'required|exists:servicios,id',
            'fecha'          => 'required|date',
            'hora'           => 'required'
        ]);

        // Convertimos la fecha a objeto DateTime para extraer el día de la semana
        // format('N') devuelve 1=Lunes, 2=Martes ... 7=Domingo (estándar ISO 8601)
        $fecha = new \DateTime($request->fecha);
        $diaSemana = (int)$fecha->format('N');

        // Comprobamos si el profesional tiene disponibilidad en ese día y franja horaria
        // Buscamos un registro en la tabla disponibilidades que cumpla:
        // - sea del profesional seleccionado
        // - sea del día de la semana correcto
        // - la hora solicitada esté dentro del rango hora_inicio <= hora < hora_fin
        $disponible = \App\Models\Disponibilidad::where('profesional_id', $request->profesional_id)
            ->where('dia_semana', $diaSemana)
            ->where('hora_inicio', '<=', $request->hora)
            ->where('hora_fin', '>', $request->hora)
            ->exists(); // exists() devuelve true/false sin cargar el objeto completo (más eficiente)

        // Si no hay disponibilidad devolvemos error 422 (Unprocessable Entity)
        if (!$disponible) {
            return response()->json([
                'message' => 'El profesional no está disponible en ese día y hora'
            ], 422);
        }

        // Comprobamos que no exista ya una cita para ese profesional, fecha y hora
        // Ignoramos las citas canceladas porque liberan el hueco
        $solapamiento = \App\Models\Cita::where('profesional_id', $request->profesional_id)
            ->where('fecha', $request->fecha)
            ->where('hora', $request->hora)
            ->where('estado', '!=', 'cancelada')
            ->exists();

        // Si hay solapamiento devolvemos error 422 con mensaje descriptivo
        if ($solapamiento) {
            return response()->json([
                'message' => 'El profesional ya tiene una cita en esa fecha y hora'
            ], 422);
        }

        // Si pasan ambas validaciones, creamos la cita en la base de datos
        // El cliente_id se obtiene del token, no del body, por seguridad
        // El estado inicial siempre es 'pendiente'
        $cita = \App\Models\Cita::create([
            'cliente_id'     => $request->user()->id,
            'profesional_id' => $request->profesional_id,
            'servicio_id'    => $request->servicio_id,
            'fecha'          => $request->fecha,
            'hora'           => $request->hora,
            'estado'         => 'pendiente'
        ]);

        // Devolvemos la cita creada con sus relaciones cargadas y código 201 (Created)
        return response()->json($cita->load(['profesional', 'servicio']), 201);
    }

    /**
     * GET /api/citas/{id}
     * Devuelve una cita concreta con todas sus relaciones.
     * findOrFail lanza un 404 automático si no existe el id.
     */
    public function show($id)
    {
        $cita = Cita::with(['cliente', 'profesional.user', 'servicio'])->findOrFail($id);
        return response()->json($cita);
    }

    /**
     * PUT /api/citas/{id}
     * Actualiza el estado de una cita (pendiente, confirmada, cancelada).
     * Usado principalmente por el profesional y el admin desde sus dashboards.
     */
    public function update(Request $request, $id)
    {
        // Buscamos la cita o devolvemos 404 si no existe
        $cita = Cita::findOrFail($id);

        // Validamos que el estado sea uno de los tres valores permitidos
        $request->validate([
            'estado' => 'in:pendiente,confirmada,cancelada'
        ]);

        // Actualizamos solo los campos que vienen en la petición
        $cita->update($request->all());
        return response()->json($cita);
    }

    /**
     * DELETE /api/citas/{id}
     * Elimina una cita de la base de datos permanentemente.
     */
    public function destroy($id)
    {
        // findOrFail lanza 404 si no existe, delete() elimina el registro
        Cita::findOrFail($id)->delete();
        return response()->json(['message' => 'Cita eliminada']);
    }

    /**
     * GET /api/citas-ocupadas/{profesional_id}/{fecha}
     * Devuelve un array con las horas ya reservadas de un profesional en una fecha concreta.
     * El cliente lo usa para saber qué slots mostrar como ocupados en el calendario.
     */
    public function horasOcupadas($profesional_id, $fecha)
    {
        $horas = Cita::where('profesional_id', $profesional_id)
            ->where('fecha', $fecha)
            ->where('estado', '!=', 'cancelada') // Las canceladas no bloquean el hueco
            ->pluck('hora')                       // Extrae solo el campo 'hora' de cada fila
            ->map(fn($h) => substr($h, 0, 5))    // Convierte '08:00:00' a '08:00'
            ->toArray();                           // Convierte la colección a array PHP

        // Devuelve algo como: ["08:00", "09:30", "11:00"]
        return response()->json($horas);
    }
}