<?php

namespace Adams\Przelewy24\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Adams\Przelewy24\Facades\Facade as Przelewy24;

class AuthorizePrzelewy24Servers
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $isAllowed = in_array($request->getClientIp(), 
            Przelewy24::getAllowedAddresses()
        );

        if (Przelewy24::isLive() && ! $isAllowed) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}