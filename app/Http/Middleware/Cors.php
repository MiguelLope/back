<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    // En tu middleware Cors.php
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = [
            'https://front-production-cc8a.up.railway.app',
            'https://back-production-3ec7.up.railway.app'
        ];
    
        $origin = $request->headers->get('Origin');
    
        if (in_array($origin, $allowedOrigins)) {
            $response = $next($request);
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-XSRF-TOKEN');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            return $response;
        }
    
        return response('Acceso no autorizado', 403);
    }
}
