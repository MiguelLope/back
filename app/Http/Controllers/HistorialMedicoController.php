<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Especialista;
use App\Models\HistorialMedico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HistorialMedicoController
{
    public function index()
    {
        $historiales = HistorialMedico::with('usuario', 'especialista', 'cita')->get();
        return response()->json($historiales);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'id_especialista' => 'required|exists:especialistas,id_especialista',
            'id_cita' => 'required|exists:citas,id_cita',
            'diagnostico' => 'required|string',
            'tratamiento' => 'required|string',
            'fecha' => 'required|date',
        ]);

        // Obtener el id_cita desde el array validado
        $id_cita = $validated['id_cita'];

        // Buscar la cita en la base de datos
        $cita = Cita::findOrFail($id_cita);

        // Realizar alguna acción con la cita, como actualizar el estado a 'confirmado'
        if ($cita->estado !== 'completada') {
            $cita->update([
                'estado' => (string) 'completada',
            ]);
        }

        // Preparar los datos para el historial médico
        $historialData = [
            'id_usuario' => $validated['id_usuario'],
            'id_especialista' => $validated['id_especialista'],
            'id_cita' => $validated['id_cita'],
            'diagnostico' => $validated['diagnostico'],
            'tratamiento' => $validated['tratamiento'],
            'fecha' => $validated['fecha'],
        ];

        // Crear el historial médico
        $historial = HistorialMedico::create($historialData);

        // Responder con un mensaje de éxito
        return response()->json(['message' => 'Historial médico creado con éxito', 'historial' => $historial], 201);
    }

    public function show($id)
    {
        $historial = HistorialMedico::with('cita', 'usuarios', 'especialista')->findOrFail($id);
        return response()->json($historial);
    }

    public function update(Request $request, $id)
    {
        $historial = HistorialMedico::findOrFail($id);

        $validated = $request->validate([
            'diagnostico' => 'required|string',
            'tratamiento' => 'required|string',
            'fecha' => 'nullable|date',
        ]);

        $historial->update($validated);
        return response()->json($historial);
    }

    public function destroy($id)
    {
        $historial = HistorialMedico::findOrFail($id);
        $historial->delete();
        return response()->json(['message' => 'Historial médico eliminado']);
    }

    public function getHistorialPaciente($id)
    {
        $historiales = HistorialMedico::with([
            'usuario:id_usuario,nombre,email,telefono',
            'especialista:id_especialista,id_usuario,especialidad,id_consultorio',
            'especialista.usuario:id_usuario,nombre,email,telefono',
            'cita:id_cita,id_usuario,id_especialista,id_consultorio,fecha_hora,estado,motivo',
            'cita.consultorio:id_consultorio,nombre' // Agregar el consultorio
        ])
            ->where('id_usuario', $id)->get();
        return response()->json($historiales);
    }

    public function getHistorialEspecialista($id)
    {
        // Buscar al especialista basado en el id_usuario
        $especialista = Especialista::with('usuario')->where('id_usuario', $id)->first();

        if (!$especialista) {
            return response()->json(['error' => 'Especialista no encontrado'], 404);
        }

        // Obtener el historial médico con las relaciones necesarias
        $historiales = HistorialMedico::with([
            'usuario:id_usuario,nombre,email,telefono',
            'especialista:id_especialista,especialidad,id_consultorio',
            'cita:id_cita,id_usuario,id_especialista,id_consultorio,fecha_hora,estado,motivo',
            'cita.consultorio:id_consultorio,nombre' // Agregar el consultorio
        ])
            ->where('id_especialista', $especialista->id_especialista)
            ->get();

        return response()->json($historiales);
    }



    /**
     * Obtiene todo el historial médico disponible en la base de datos.
     * Solo debe ser accesible por el administrador.
     */
    public function getHistorialCompleto()
    {
     
        try {
            // Obtener todo el historial con las relaciones necesarias
            $historiales = HistorialMedico::with([
                'usuario:id_usuario,nombre,email,telefono',
                'especialista:id_especialista,id_usuario,especialidad,id_consultorio',
                'especialista.usuario:id_usuario,nombre,email,telefono',
                'cita:id_cita,id_usuario,id_especialista,id_consultorio,fecha_hora,estado,motivo',
                'cita.consultorio:id_consultorio,nombre'
            ])->get();
            // Verificar si hay registros
            if ($historiales->isEmpty()) {
                return response()->json(['mensaje' => 'No hay historial médico registrado'], 404);
            }

            return response()->json($historiales);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ocurrió un error al obtener el historial'], 500);
        }
    }
}
