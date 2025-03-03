<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Verification;
use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController
{
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
        ]);
    
        // Buscar el usuario
        $usuario = Usuario::where('email', $request->email)->first();
    
        // Verificar si el usuario realmente existe (en caso de que la validación no sea suficiente)
        if (!$usuario) {
            return response()->json(['error' => 'El correo electrónico no está registrado.'], 404);
        }
    
        // Eliminar códigos previos del usuario
        Verification::where('id_usuario', $usuario->id_usuario)->delete();
    
        // Generar el nuevo código de verificación
        $verification = Verification::generateCode($usuario->id_usuario);
    
        // Enviar el código por correo
        Mail::to($usuario->email)->send(new VerificationCodeMail($verification->codigo));
    
        return response()->json(['message' => 'Código de verificación enviado']);
    }
    


    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
            'codigo' => 'required|string',
            'nueva_contraseña' => 'required|min:6|confirmed',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();
        $verification = Verification::where('id_usuario', $usuario->id_usuario)
            ->where('codigo', $request->codigo)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return response()->json(['error' => 'Código inválido o expirado'], 400);
        }
        // Código válido: eliminar el registro y confirmar la verificación
         // Restablecer la contraseña
         Usuario::where('email', $request->email)->update([
            'contraseña' => bcrypt($request->nueva_contraseña)
        ]);
        $verification->delete();

        return response()->json(['message' => 'Contraseña restablecida exitosamente']);
    }
}
