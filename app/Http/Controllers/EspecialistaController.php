<?php

namespace App\Http\Controllers;

use App\Models\Especialista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EspecialistaController
{
    /**
     * Mostrar detalles de un especialista.
     */
    public function show($id)
    {
        $especialista = Especialista::with('usuario', 'consultorio')
            ->where('id_usuario', $id)
            ->firstOr(function () {
                abort(404, 'Especialista no encontrado');
            });

        return response()->json([
            'especialista' => [
                'id_especialista' => $especialista->id_especialista,
                'id_usuario' => $especialista->id_usuario,
                'nombre' => $especialista->usuario->nombre,
                'especialidad' => $especialista->especialidad,
                'horario_inicio' => $especialista->horario_inicio,
                'horario_fin' => $especialista->horario_fin,
                'dias_trabajo' => json_decode($especialista->dias_trabajo, true) ?? [],
                'consultorio' => $especialista->consultorio ? [
                    'nombre' => $especialista->consultorio->nombre,
                    'ubicacion' => $especialista->consultorio->ubicacion,
                ] : null,
            ],
        ]);
    }


    /**
     * Actualizar información del especialista.
     */
    public function update(Request $request, $id)
    {
        $especialista = $this->findEspecialista($id);

        $validated = $request->validate([
            'especialidad' => 'nullable|string|max:255',
            'id_consultorio' => 'nullable|exists:consultorios,id',
        ]);

        $especialista->update($validated);

        return response()->json($especialista);
    }

    /**
     * Actualizar el consultorio asignado a un especialista.
     */
    public function actualizarConsultorio(Request $request, $id_especialista)
    {
        $especialista = $this->findEspecialista($id_especialista);

        $validated = $request->validate([
            'id_consultorio' => 'required|exists:consultorios,id_consultorio',
        ]);

        $nuevo_consultorio = $validated['id_consultorio'];

        // Si el especialista no tiene horario asignado, se le asigna el consultorio directamente
        if (empty($especialista->horario_inicio) || empty($especialista->horario_fin)) {
            $especialista->update(['id_consultorio' => $nuevo_consultorio]);

            return response()->json([
                'message' => 'Consultorio asignado correctamente (sin validación de horarios)',
                'especialista' => $especialista,
            ]);
        }

        // Verificar si hay conflicto en el nuevo consultorio solo si tiene horario asignado
        $conflicto = Especialista::where('id_consultorio', $nuevo_consultorio)
            ->where('id_especialista', '!=', $id_especialista)
            ->where(function ($query) use ($especialista) {
                $query->where(function ($subQuery) use ($especialista) {
                    $horario_inicio = $especialista->horario_inicio;
                    $horario_fin = $especialista->horario_fin;
                    $pasa_medianoche = strtotime($horario_fin) < strtotime($horario_inicio);

                    if ($pasa_medianoche) {
                        // Caso especial: turno cruza medianoche (Ejemplo: 20:00 - 02:00)
                        $subQuery->whereBetween('horario_inicio', [$horario_inicio, '23:59'])
                            ->orWhereBetween('horario_fin', ['00:00', $horario_fin])
                            ->orWhere(function ($nestedQuery) use ($horario_inicio, $horario_fin) {
                                $nestedQuery->where('horario_inicio', '<=', $horario_inicio)
                                    ->where('horario_fin', '>=', $horario_fin);
                            });
                    } else {
                        // Caso normal: turno dentro del mismo día
                        $subQuery->whereBetween('horario_inicio', [$horario_inicio, $horario_fin])
                            ->orWhereBetween('horario_fin', [$horario_inicio, $horario_fin])
                            ->orWhere(function ($nestedQuery) use ($horario_inicio, $horario_fin) {
                                $nestedQuery->where('horario_inicio', '<=', $horario_inicio)
                                    ->where('horario_fin', '>=', $horario_fin);
                            });
                    }
                })
                    ->whereRaw("JSON_OVERLAPS(dias_trabajo, ?)", [json_encode(json_decode($especialista->dias_trabajo, true) ?? [])]);
            })
            ->exists();

        if ($conflicto) {
            abort(409, 'El especialista tiene un horario que entra en conflicto con otro en el nuevo consultorio.');
        }

        // Si no hay conflictos, actualizar el consultorio
        $especialista->update(['id_consultorio' => $nuevo_consultorio]);

        return response()->json([
            'message' => 'Consultorio asignado correctamente',
            'especialista' => $especialista,
        ]);
    }



    /**
     * Actualizar el horario de un especialista.
     */
    public function actualizarHorario(Request $request, $id_especialista)
    {
        Log::info($request->all());
        $especialista = $this->findEspecialista($id_especialista);

        if (!$especialista->id_consultorio) {
            abort(400, 'El especialista no tiene un consultorio asignado');
        }

        // Validación de la entrada
        $validated = $request->validate([
            'horario_inicio' => 'required|date_format:H:i',
            'horario_fin' => 'required|date_format:H:i|not_in:00:00',
            'dias_trabajo' => 'required|array|min:1',
            'dias_trabajo.*' => 'string|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
        ]);

        $horario_inicio = $validated['horario_inicio'];
        $horario_fin = $validated['horario_fin'];
        $dias_trabajo = $validated['dias_trabajo'];

        // Ajustar el fin si pasa de medianoche
        $pasa_medianoche = strtotime($horario_fin) < strtotime($horario_inicio);

        // Buscar conflicto con otros especialistas en el mismo consultorio
        $conflicto = Especialista::where('id_consultorio', $especialista->id_consultorio)
            ->where('id_especialista', '!=', $id_especialista)
            ->where(function ($query) use ($horario_inicio, $horario_fin, $dias_trabajo, $pasa_medianoche) {
                $query->where(function ($subQuery) use ($horario_inicio, $horario_fin, $pasa_medianoche) {
                    if ($pasa_medianoche) {
                        // Caso especial: horario pasa de medianoche (Ejemplo: 20:00 - 02:00)
                        $subQuery->whereBetween('horario_inicio', [$horario_inicio, '23:59'])
                            ->orWhereBetween('horario_fin', ['00:00', $horario_fin])
                            ->orWhere(function ($nestedQuery) use ($horario_inicio, $horario_fin) {
                                $nestedQuery->where('horario_inicio', '<=', $horario_inicio)
                                    ->where('horario_fin', '>=', $horario_fin);
                            });
                    } else {
                        // Caso normal: horario dentro del mismo día
                        $subQuery->whereBetween('horario_inicio', [$horario_inicio, $horario_fin])
                            ->orWhereBetween('horario_fin', [$horario_inicio, $horario_fin])
                            ->orWhere(function ($nestedQuery) use ($horario_inicio, $horario_fin) {
                                $nestedQuery->where('horario_inicio', '<=', $horario_inicio)
                                    ->where('horario_fin', '>=', $horario_fin);
                            });
                    }
                })
                    ->whereRaw("JSON_OVERLAPS(dias_trabajo, ?)", [json_encode($dias_trabajo)]);
            })
            ->exists();

        if ($conflicto) {
            abort(409, 'El horario y días de trabajo se cruzan con otro especialista en el mismo consultorio');
        }

        // Guardar los datos, convirtiendo el array de días a formato JSON
        $especialista->update([
            'horario_inicio' => $horario_inicio,
            'horario_fin' => $horario_fin,
            'dias_trabajo' => json_encode($dias_trabajo),
        ]);

        return response()->json([
            'message' => 'Horario actualizado correctamente',
            'especialista' => $especialista,
        ]);
    }



    /**
     * Eliminar un especialista.
     */
    public function destroy($id)
    {
        $especialista = $this->findEspecialista($id);
        $especialista->delete();

        return response()->json(['message' => 'Especialista eliminado']);
    }

    /**
     * Método auxiliar para encontrar un especialista o devolver error 404.
     */
    private function findEspecialista($id)
    {
        return Especialista::findOrFail($id);
    }

    public function findEspecialistaDating($id)
    {


        try {
            // Buscar al especialista por su ID
            $especialista = Especialista::with('usuario')
                ->where('id_especialista', $id)
                ->firstOr(function () {
                    abort(404, 'Especialista no encontrado');
                });
            // Retornar la información del especialista
            return response()->json([
                'id_especialista' => $especialista->id_especialista,
                'nombre' => $especialista->usuario->nombre,
                // Agregar más datos si es necesario
            ]);
        } catch (\Exception $e) {
            // Manejar errores si el especialista no se encuentra
            return response()->json([
                'error' => 'Especialista no encontrado.',
            ], 404);
        }
    }
}
