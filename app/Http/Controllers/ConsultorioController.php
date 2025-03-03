<?php
namespace App\Http\Controllers;

use App\Models\Consultorio;
use App\Models\Especialista;
use Illuminate\Http\Request;

class ConsultorioController
{
    public function index()
    {
        $consultorios = Consultorio::all();
        return response()->json($consultorios);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
        ]);

        $consultorio = Consultorio::create($validated);
        return response()->json($consultorio, 201);
    }

    public function show($id)
    {
        $consultorio = Consultorio::findOrFail($id);
        return response()->json($consultorio);
    }

    public function update(Request $request, $id)
    {
        $consultorio = Consultorio::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
        ]);

        $consultorio->update($validated);
        return response()->json($consultorio);
    }

    public function destroy($id)
    {
        $consultorio = Consultorio::findOrFail($id);
        $consultorio->delete();
        return response()->json(['message' => 'Consultorio eliminado']);
    }

    public function obtenerEspecialistas($id_consultorio)
    {
        $especialistas = Especialista::with('usuario')->where('id_consultorio', $id_consultorio)->get();
        return response()->json([
            'especialistas' => $especialistas
        ], 200);
    }



}
