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
    public function handle(Request $request, Closure $next)
    {
        // Manejar solicitudes preflight OPTIONS
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', 'https://front-production-cc8a.up.railway.app')
                ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE')
                ->header('Access-Control-Allow-Headers', 'Content-Type, X-Auth-Token, Origin, Authorization')
                ->header('Access-Control-Allow-Credentials', 'true');
        }
    
        $response = $next($request);
    
        $response->headers->set('Access-Control-Allow-Origin', 'https://front-production-cc8a.up.railway.app');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Auth-Token, Origin, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
    
        return $response;
    }
}
