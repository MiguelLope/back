<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PagoController
{


        public function index()
    {
        $pagos = Pago::with(['cita.paciente', 'cita.especialista.usuario', 'cita.consultorio'])->get();
        return response()->json($pagos);
    }
    
    public function store(Request $request)
    {
        
        $request->validate([
            'id_cita' => 'required|exists:citas,id_cita',
            'monto' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
            'estado' => 'required|in:pendiente,completado,fallido', // Validación del estado
        ]);
    
        $pago = Pago::create([
            'id_cita' => $request->id_cita,
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'estado' => $request->estado,  // Agregar el estado recibido
        ]);
    
        return response()->json(['message' => 'Pago registrado exitosamente', 'pago' => $pago], 201);
    }


    public function completarPago($id_pago)
    {
        $pago = Pago::find($id_pago);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        if ($pago->estado === 'completado') {
            return response()->json(['message' => 'El pago ya ha sido completado'], 400);
        }

        $pago->update(['estado' => 'completado']);

        return response()->json(['message' => 'Pago completado exitosamente', 'pago' => $pago]);
    }
    
}
