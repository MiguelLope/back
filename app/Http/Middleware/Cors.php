<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = [
            env('FRONT_URL', 'https://front-production-cc8a.up.railway.app'),
            // Agrega otros orígenes permitidos si es necesario
        ];

        $origin = $request->headers->get('Origin');

        if (in_array($origin, $allowedOrigins)) {
            $response = $next($request);
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Auth-Token, Origin, Authorization, withCredentials');
            $response->headers->set('Access-Control-Allow-Credentials', 'true'); // Importante para credenciales
            return $response;
        }

        return $next($request); // Si el origen no está permitido, continúa sin modificar las cabeceras
    }
}