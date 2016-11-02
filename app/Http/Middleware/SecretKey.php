<?php

namespace App\Http\Middleware;

use Closure;

class SecretKey
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
        if($request->get('secretKey') != config('app.secretKey')) return response()->json('Invalid Secret Key');
        return $next($request);
    }
}
