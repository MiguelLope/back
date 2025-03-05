<?php

namespace App\Http\Controllers;

use App\Models\Especialista;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class UsuarioController
{
    public function index()
    {
        $usuarios = Usuario::all();
        return response()->json($usuarios);
    }

    public function store(Request $request)
    {
        // Validación de los datos de usuario
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'curp' => 'required|string|unique:usuarios,curp',
            'contraseña' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:15',
            'tipo_usuario' => 'required|in:admin,especialista,paciente',
            'especialidad' => 'required_if:tipo_usuario,especialista|string|max:100', // Validar especialidad solo si es especialista
        ]);

        // Encriptar la contraseña
        $validated['contraseña'] = bcrypt($validated['contraseña']);

        // Crear el usuario
        $usuario = Usuario::create($validated);

        // Crear el especialista si aplica
        if ($validated['tipo_usuario'] === 'especialista') {
            $especialista = Especialista::create([
                'id_usuario' => $usuario->id_usuario,
                'especialidad' => $validated['especialidad'],
            ]);

            // Agregar la especialidad al nivel superior del usuario
            $usuario->especialidad = $especialista->especialidad;
        }

        // Respuesta con los datos del usuario creado (incluida la especialidad si aplica)
        return response()->json($usuario, 201);
    }

    public function show($id)
    {
        $usuario = Usuario::findOrFail($id);
        return response()->json($usuario);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:usuarios,email,' . $id . ',id_usuario',
            'telefono' => 'nullable|string|max:20',
            'tipo_usuario' => 'nullable|in:paciente,especialista,admin',
            'contraseña' => 'nullable|string'
        ]);


        $usuario = Usuario::find($id);

        if ($usuario) {
            $usuario->nombre = $validated['nombre'] ?? $usuario->nombre;
            $usuario->email = $validated['email'] ?? $usuario->email;
            $usuario->telefono = $validated['telefono'] ?? $usuario->telefono;
            $usuario->tipo_usuario = $validated['tipo_usuario'] ?? $usuario->tipo_usuario;

            // Solo actualiza la contraseña si se ha proporcionado un nuevo valor
            if (!empty($validated['contraseña'])) {
                $usuario->contraseña = bcrypt($validated['contraseña']); // Hashea la nueva contraseña
            }

            $usuario->save();

            return response()->json(['message' => 'Usuario actualizado correctamente']);
        } else {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required',
            'password' => 'required|string',
        ]);

        // Intentamos encontrar al usuario por email
        $usuario = Usuario::where('email', $validated['email'])->first();

        // Si no se encuentra por email, se intenta por CURP
        if (!$usuario) {
            $usuario = Usuario::where('curp', $validated['email'])->first();
        }

        // Si no se encuentra el usuario o la contraseña no coincide, se retorna un error
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no existe'], 401);
        } elseif (!password_verify($validated['password'], $usuario->contraseña)) {
            return response()->json(['message' => 'Contraseña Incorrecta'], 401);
        }

        return response()->json(['usuario' => $usuario]);
    }


    public function getAdministradores()
    {
        $administradores = Usuario::where('tipo_usuario', 'admin')->get();
        return response()->json($administradores);
    }

    public function getPacientes()
    {
        $pacientes = Usuario::where('tipo_usuario', 'paciente')->get();
        return response()->json($pacientes);
    }

    public function getEspecialistas()
    {
        $especialistas = Usuario::where('tipo_usuario', 'especialista')
            ->with('especialista:id_usuario,especialidad')
            ->get()
            ->map(function ($usuario) {
                $usuario->especialidad = $usuario->especialista->especialidad ?? null; // Agregar especialidad al nivel superior
                unset($usuario->especialista); // Eliminar la relación anidada
                return $usuario;
            });

        return response()->json($especialistas);
    }
}
