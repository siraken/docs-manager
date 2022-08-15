<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddResponseHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->header('Server', 'Novalumo Server');
        // $response->header('Access-Control-Allow-Origin', 'http://localhost:3000');
        // $response->header('Access-Control-Allow-Methods', 'GET,POST,HEAD,OPTIONS');

        return $response;
    }
}
