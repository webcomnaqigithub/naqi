<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Debug\Exception\FatalThrowableError;

use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
class AssignGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    // public function handle($request, Closure $next)
    // {
    //     return $next($request);
    // }
    public function handle($request, Closure $next, $guard = null)
    {
        if($guard != null)
                {
                    // JWTAuth::parseToken()->authenticate();
                    auth()->shouldUse($guard);
                }
                return $next($request);
            
        
    }   
}
