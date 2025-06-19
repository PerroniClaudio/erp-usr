<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSocialiteSession {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        // Assicurati che la sessione sia inizializzata
        $request->session()->start();

        // Per le richieste di callback di Microsoft, verifica che ci sia uno stato
        if ($request->routeIs('auth.microsoft.callback') && !$request->session()->has('state')) {
            // Se non c'è uno stato nella sessione, prova a recuperarlo dalla cache
            $state = $request->get('state');
            if ($state) {
                $request->session()->put('state', $state);
            }
        }

        return $next($request);
    }
}
