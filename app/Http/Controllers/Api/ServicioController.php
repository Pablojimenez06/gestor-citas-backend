<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    // GET /api/servicios
    public function index()
    {
        return response()->json(
            Servicio::with('profesional')->get()
        );
    }

    // POST /api/servicios
    public function store(Request $request)
    {
        $request->validate([
            'profesional_id' => 'required|exists:profesionals,id',
            'nombre'         => 'required|string',
            'descripcion'    => 'nullable|string',
            'duracion'       => 'required|integer',
            'precio'         => 'required|numeric'
        ]);

        $servicio = Servicio::create($request->all());
        return response()->json($servicio, 201);
    }

    // GET /api/servicios/{id}
    public function show($id)
    {
        $servicio = Servicio::with('profesional')->findOrFail($id);
        return response()->json($servicio);
    }

    // PUT /api/servicios/{id}
    public function update(Request $request, $id)
    {
        $servicio = Servicio::findOrFail($id);
        $servicio->update($request->all());
        return response()->json($servicio);
    }

    // DELETE /api/servicios/{id}
    public function destroy($id)
    {
        Servicio::findOrFail($id)->delete();
        return response()->json(['message' => 'Servicio eliminado']);
    }
}