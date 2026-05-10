<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profesional;
use Illuminate\Http\Request;

class ProfesionalController extends Controller
{
    // GET /api/profesionales
    public function index()
    {
        return response()->json(
            Profesional::with(['user', 'servicios'])->get()
        );
    }

    // POST /api/profesionales
    public function store(Request $request)
    {
        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'especialidad' => 'required|string',
            'telefono'     => 'nullable|string'
        ]);

        $profesional = Profesional::create($request->all());
        return response()->json($profesional, 201);
    }

    // GET /api/profesionales/{id}
    public function show($id)
    {
        $profesional = Profesional::with(['user', 'servicios', 'citas'])->findOrFail($id);
        return response()->json($profesional);
    }

    // PUT /api/profesionales/{id}
    public function update(Request $request, $id)
    {
        $profesional = Profesional::findOrFail($id);
        $profesional->update($request->all());
        return response()->json($profesional);
    }

    // DELETE /api/profesionales/{id}
    public function destroy($id)
    {
        Profesional::findOrFail($id)->delete();
        return response()->json(['message' => 'Profesional eliminado']);
    }
}