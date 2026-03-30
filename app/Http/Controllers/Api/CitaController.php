<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cita;



class CitaController extends Controller
{
    //
    public function index()
    {
        return Cita::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email',
            'fecha' => 'required|date',
            'hora' => 'required'
        ]);

        $cita = Cita::create($validated);

        return response()->json($cita, 201);
    }


    public function destroy($id)
    {
        $cita = Cita::findOrFail($id);
        $cita->delete();

        return response()->json(['mensaje' => 'Cita eliminada']);
    }

    public function update(Request $request, $id)
    {
        $cita = \App\Models\Cita::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email',
            'fecha' => 'required|date',
            'hora' => 'required'
        ]);

        $cita->update($validated);

        return response()->json($cita);
    }

}
