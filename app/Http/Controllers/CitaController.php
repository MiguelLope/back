<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Especialista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CitaController
{
    public function index()
    {
        $citas = Cita::with('paciente', 'especialista', 'consultorio')->get();
        return response()->json($citas);
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'id_especialista' => 'required|exists:especialistas,id_especialista',
            'id_consultorio' => 'required|exists:consultorios,id_consultorio',
            'fecha_hora' => 'required|date',
            'estado' => 'required|in:pendiente,confirmada,cancelada',
            'motivo' => 'nullable|string|max:255',
        ]);

        // Verificar si la fecha y hora es anterior a la actual
        if (strtotime($validated['fecha_hora']) < strtotime(now())) {
            return response()->json(['error' => 'No puedes agendar una cita en una fecha y hora pasada.'], 422);
        }

        // Verificar que el usuario no tenga más de 3 citas activas
        $citasUsuario = Cita::where('id_usuario', $validated['id_usuario'])
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->count();

        if ($citasUsuario >= 3) {
            return response()->json(['error' => 'No puedes agendar más de 3 citas activas.'], 422);
        }

        // Obtener el horario del especialista
        $especialista = Especialista::findOrFail($validated['id_especialista']);

        // Extraer la hora de la cita
        $horaCita = date('H:i', strtotime($validated['fecha_hora']));
        $horarioInicio = $especialista->horario_inicio;
        $horarioFin = $especialista->horario_fin;

        // Verificar si la hora está dentro del rango permitido
        $esValida = false;

        if ($horarioInicio < $horarioFin) {
            // Caso normal (ejemplo: 08:00 - 18:00)
            $esValida = ($horaCita >= $horarioInicio && $horaCita <= $horarioFin);
            Log::info($esValida);
        } else {
            // Caso en que el horario cruza la medianoche (ejemplo: 22:00 - 06:00)
            $esValida = ($horaCita >= $horarioInicio || $horaCita <= $horarioFin);
        }

        if (!$esValida) {
            return response()->json(['error' => 'La hora de la cita está fuera del horario del especialista.'], 422);
        }

        // Verificar si hay conflictos de citas para el especialista o el consultorio en la misma fecha y hora
        $conflicto = Cita::where(function ($query) use ($validated) {
            $query->where('id_especialista', $validated['id_especialista'])
                ->orWhere('id_consultorio', $validated['id_consultorio']);
        })->where('fecha_hora', $validated['fecha_hora'])->exists();

        if ($conflicto) {
            return response()->json(['error' => 'Ya existe una cita agendada en esta fecha y hora con el mismo especialista o en el mismo consultorio.'], 422);
        }

        // Crear la cita si pasa la validación
        $cita = Cita::create($validated);
        return response()->json($cita, 201);
    }



    public function showEspecialista($id)
    {
        $cita = Cita::with('paciente', 'especialista', 'consultorio')->findOrFail($id);
        return response()->json($cita);
    }


    public function show($id)
    {
        $cita = Cita::with('paciente', 'especialista', 'consultorio')->findOrFail($id);
        $c = [
            'paciente' => $cita->paciente->nombre,
            'especialista' => $cita->especialista->usuario->nombre,
            'consultorio' => $cita->consultorio->nombre,
            'fecha_hora' => $cita->fecha_hora,
            'estado' => $cita->estado,
            'motivo' => $cita->motivo,

        ];
        return response()->json([
            'cita' => $c,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);

        $validated = $request->validate([
            'fecha_hora' => 'nullable|date',
            'estado' => 'nullable|in:pendiente,confirmada,cancelada',
            'motivo' => 'nullable|string|max:255',
        ]);

        $cita->update($validated);
        return response()->json($cita);
    }

    public function destroy($id)
    {
        $cita = Cita::findOrFail($id);
        $cita->delete();
        return response()->json(['message' => 'Cita eliminada']);
    }


    public function citasPendientesPorEspecialista($id_especialista)
    {
        $especialista = Especialista::with('usuario')->where('id_usuario', $id_especialista)->get()->first();
        $citas = Cita::with('paciente', 'consultorio', 'especialista')
            ->where('id_especialista', $especialista->id_especialista)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->orderBy('fecha_hora', 'asc')
            ->get();
        Log::info($citas);
        return response()->json($citas);
    }

    public function citasPendientesPorPaciente($id_usuario)
    {
        $citas = Cita::with('paciente', 'consultorio', 'especialista')
            ->where('id_usuario', $id_usuario)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->orderBy('fecha_hora', 'asc')
            ->get();

        Log::info($citas);
        return response()->json($citas);
    }

    public function citasPendientesPorAdmin()
    {
        $citas = Cita::with('paciente', 'consultorio', 'especialista')
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->orderBy('fecha_hora', 'asc')
            ->get();
        return response()->json($citas);
    }
}
