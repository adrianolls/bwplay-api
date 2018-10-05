<?php

namespace App\Http\Middleware;

use Closure;

class APITokenMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('apikey') === env('API_KEY'))
        {
            return $next($request);
        }
        return response('Acesso não autorizado', 401);
    }

}
