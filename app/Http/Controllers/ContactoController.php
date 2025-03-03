<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contacto;

class ContactoController
{
    public function index()
    {
        $contacto = Contacto::first();
        return response()->json($contacto);
    }

    public function update(Request $request)
    {
        $request->validate([
            'ubicacion' => 'required|string|max:150',
            'email' => 'required|email|max:100',
            'telefono' => 'nullable|string|max:15',
        ]);

        $contacto = Contacto::firstOrNew([]);
        $contacto->ubicacion = $request->ubicacion;
        $contacto->email = $request->email;
        $contacto->telefono = $request->telefono;
        $contacto->save();

        $contacto->ubicacion = $request->ubicacion;
        $contacto->email = $request->email;
        $contacto->telefono = $request->telefono;
        $contacto->save();

        return redirect()->back()->with('success', 'Contacto actualizado correctamente');
    }
}
