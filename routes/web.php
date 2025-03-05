<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ConsultorioController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\EspecialistaController;
use App\Http\Controllers\HistorialMedicoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\UsuarioController;
use App\Models\Pago;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;

Route::get('/debug-sanctum-url', function() {
    return response()->json([
        'current_application_url' => Sanctum::currentApplicationUrlWithPort(),
        'app_url_from_env' => env('APP_URL'),
        'trusted_domains' => config('sanctum.stateful')
    ]);
});

Route::post('api/enviar-codigo', [AuthController::class, 'sendVerificationCode']);
Route::post('api/verificar-codigo', [AuthController::class, 'verifyCode']);

Route::resource('api/pagos', PagoController::class);

Route::put('api/pagos/{id_pago}/completar', [PagoController::class, 'completarPago']);

Route::get('api/contacto', [ContactoController::class, 'index']);
Route::post('api/contacto', [ContactoController::class, 'update']);


// Rutas para manejar recursos utilizando controladores tipo resource
Route::resource('api/usuarios', UsuarioController::class);
Route::resource('api/consultorios', ConsultorioController::class);
Route::resource('api/especialistas', EspecialistaController::class);
Route::resource('api/citas', CitaController::class);
Route::resource('api/historial', HistorialMedicoController::class);

// Ruta para obtener los especialistas de un consultorio específico
Route::get('api/consultorios/{id_consultorio}/especialistas', [ConsultorioController::class, 'obtenerEspecialistas']);

// Ruta para iniciar sesión
Route::post('api/login', [UsuarioController::class, 'login']);

// Rutas para actualizar información de los especialistas
Route::put('api/especialistas/{id_usuario}/consultorio', [EspecialistaController::class, 'actualizarConsultorio']);
Route::put('api/especialistas/{id_usuario}/horario', [EspecialistaController::class, 'actualizarHorario']);

// Ruta para obtener la lista de administradores
Route::get('api/administradores', [UsuarioController::class, 'getAdministradores']);

// Rutas para obtener la lista de pacientes y sus citas pendientes
Route::get('api/pacientes', [UsuarioController::class, 'getPacientes']);
Route::get('api/paciente/citas/{id_especialista}', [CitaController::class, 'citasPendientesPorPaciente']);

// Rutas para obtener información sobre los especialistas y sus citas
Route::get('api/especialistas', [UsuarioController::class, 'getEspecialistas']);
Route::get('api/especialistas/date/{id_especialista}', [EspecialistaController::class, 'findEspecialistaDating']);
Route::get('api/especialista/citas/{id_especialista}', [CitaController::class, 'citasPendientesPorEspecialista']);
Route::get('api/especialista/citas', [CitaController::class, 'citasPendientesPorAdmin']);

// Rutas para obtener el historial médico de pacientes y especialistas
Route::get('api/historial/paciente/{id}', [HistorialMedicoController::class, 'getHistorialPaciente']);
Route::get('api/historial/especialista/{id}', [HistorialMedicoController::class, 'getHistorialEspecialista']);

// Ruta para obtener información de las citas relacionadas a un especialistas
Route::get('api/citas/especialista/{id}', [CitaController::class, 'showEspecialista']);


Route::get('api/historial/completo/{id}', [HistorialMedicoController::class, 'getHistorialCompleto']);




/*
Route::resource('especialistas', EspecialistaController::class);
Route::resource('citas', CitaController::class);
Route::resource('historiales', HistorialMedicoController::class);
*/
